<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Process\ProcessResult;
use Illuminate\Support\Facades\Process;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\note;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\table;
use function Laravel\Prompts\warning;

class DeployCommand extends Command
{
    protected $signature = 'app:deploy
        {--fresh : Run fresh install (migrate:fresh + seed)}
        {--seed : Run database seeders}
        {--skip-build : Skip npm install and asset build}
        {--force : Skip all confirmation prompts}';

    protected $description = 'Deploy the application (migrations, caching, asset build)';

    private float $startTime;

    public function handle(): int
    {
        $this->startTime = microtime(true);

        $version = config('version.version', 'unknown');
        $environment = app()->environment();

        intro("  BlubberLounge Tools — Deploy v{$version} [{$environment}]  ");

        // ── Resolve options (interactive when no flags given) ─────────
        [$fresh, $seed, $skipBuild] = $this->resolveOptions();

        // ── Show deployment plan ─────────────────────────────────────
        table(
            ['Step', 'Action'],
            [
                ['Maintenance', 'Put application offline'],
                ['Caches', 'Clear all cached data'],
                ['Database', $fresh ? 'Wipe & rebuild all tables' : 'Run pending migrations'],
                ['Seeders', ($fresh || $seed) ? 'Seed roles, permissions & defaults' : '— skip'],
                ['Storage', 'Ensure public disk symlink'],
                ['Frontend', $skipBuild ? '— skip' : 'Install deps & compile assets'],
                ['Optimize', 'Cache config, routes & views'],
                ['Online', 'Bring application back up'],
            ],
        );

        // ── Confirm ──────────────────────────────────────────────────
        if (! $this->option('force')) {
            $message = $fresh
                ? 'This will WIPE the entire database. Continue?'
                : 'Start deployment?';

            if (! confirm($message, default: ! $fresh)) {
                warning('Deployment cancelled.');

                return self::SUCCESS;
            }
        }

        // ── Build & execute steps ────────────────────────────────────
        $steps = $this->buildSteps($fresh, $seed, $skipBuild);
        $total = count($steps);

        foreach ($steps as $i => $step) {
            $counter = '[' . ($i + 1) . "/{$total}]";

            $result = spin(
                callback: fn () => ($step['task'])(),
                message: "{$counter}  {$step['label']}",
            );

            if (($step['critical'] ?? false) && $this->hasFailed($result)) {
                $this->callSilently('up');
                error("Step failed: {$step['label']}");
                warning('Application has been brought back online.');

                return self::FAILURE;
            }
        }

        // ── Done ─────────────────────────────────────────────────────
        $elapsed = round(microtime(true) - $this->startTime, 1);

        note(<<<NOTE
          Version  {$version}
          Steps    {$total} completed
          Time     {$elapsed}s
        NOTE);

        outro('Deployment complete!');

        return self::SUCCESS;
    }

    /**
     * Interactive option selection when no flags were provided.
     */
    private function resolveOptions(): array
    {
        $fresh = $this->option('fresh');
        $seed = $this->option('seed');
        $skipBuild = $this->option('skip-build');

        if ($fresh || $seed || $skipBuild || $this->option('force')) {
            return [$fresh, $seed, $skipBuild];
        }

        $choices = multiselect(
            label: 'Configure deployment',
            options: [
                'fresh' => 'Fresh install — wipe database & re-seed',
                'seed' => 'Run database seeders',
                'skip-build' => 'Skip frontend asset build',
            ],
            hint: 'Space to toggle, Enter to confirm. Empty = standard deploy.',
        );

        return [
            in_array('fresh', $choices),
            in_array('seed', $choices),
            in_array('skip-build', $choices),
        ];
    }

    /**
     * Build the ordered list of deployment steps.
     */
    private function buildSteps(bool $fresh, bool $seed, bool $skipBuild): array
    {
        $steps = [];

        $steps[] = [
            'label' => 'Enabling maintenance mode',
            'task' => fn () => $this->callSilently('down', ['--retry' => 60]),
        ];

        $steps[] = [
            'label' => 'Clearing caches',
            'task' => fn () => $this->callSilently('optimize:clear'),
        ];

        $steps[] = [
            'label' => $fresh ? 'Running fresh migrations' : 'Running migrations',
            'task' => fn () => $this->callSilently(
                $fresh ? 'migrate:fresh' : 'migrate',
                ['--force' => true],
            ),
            'critical' => true,
        ];

        if ($fresh || $seed) {
            $steps[] = [
                'label' => 'Seeding database',
                'task' => fn () => $this->callSilently('db:seed', ['--force' => true]),
                'critical' => true,
            ];
        }

        $steps[] = [
            'label' => 'Creating storage symlink',
            'task' => fn () => $this->callSilently('storage:link'),
        ];

        if (! $skipBuild) {
            $steps[] = [
                'label' => 'Installing npm dependencies',
                'task' => fn () => Process::path(base_path())->timeout(300)->run('npm install --no-audit'),
                'critical' => true,
            ];

            $steps[] = [
                'label' => 'Building frontend assets',
                'task' => fn () => Process::path(base_path())->timeout(300)->run('npm run build'),
                'critical' => true,
            ];
        }

        $steps[] = [
            'label' => 'Optimizing application',
            'task' => fn () => $this->callSilently('optimize'),
        ];

        $steps[] = [
            'label' => 'Disabling maintenance mode',
            'task' => fn () => $this->callSilently('up'),
        ];

        return $steps;
    }

    /**
     * Check whether a step result indicates failure.
     */
    private function hasFailed(mixed $result): bool
    {
        if ($result instanceof ProcessResult) {
            return $result->failed();
        }

        return is_int($result) && $result !== 0;
    }
}
