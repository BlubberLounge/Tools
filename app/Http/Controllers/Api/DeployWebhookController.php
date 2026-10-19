<?php

namespace App\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

// Plain base controller: the app's Controller adds the auth middleware, the
// webhook authenticates with its bearer token instead.
class DeployWebhookController extends Controller
{
    private const ZIP_NAME = '_deploy.zip';

    private const TEMP_DIR = '_deploy_temp';

    private const PROTECTED_PATHS = [
        '.env',
        'storage/app',
        'storage/framework/sessions',
        'storage/framework/cache',
        'storage/logs',
    ];

    public function __invoke(Request $request): JsonResponse
    {
        set_time_limit(300);

        $secret = config('deploy.webhook_secret');

        if (! $secret || ! hash_equals($secret, $request->bearerToken() ?? '')) {
            Log::warning('Deploy webhook authentication failed', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            abort(401);
        }

        $zipPath = base_path(self::ZIP_NAME);
        $log = [];

        // Extract release ZIP if present
        if (file_exists($zipPath)) {
            Artisan::call('down', ['--retry' => 60]);
            $log[] = 'Maintenance mode enabled';

            try {
                $extracted = $this->extractRelease($zipPath);
                $log[] = "Extracted {$extracted} files from release ZIP";
            } catch (\Throwable $e) {
                Artisan::call('up');
                // details stay in the server log: the CI job prints the response
                Log::error('Deploy webhook: ZIP extraction failed', ['exception' => $e]);

                return response()->json([
                    'success' => false,
                    'error' => 'ZIP extraction failed',
                    'log' => $log,
                ], 500);
            }

            Artisan::call('up');
            $log[] = 'Maintenance mode disabled';
        }

        // Run deploy command (handles migrations, caching, etc.)
        // Separate process: this request still has the old release's classes
        // loaded, so running migrations in-process mixes old and new code.
        $result = Process::path(base_path())->timeout(280)->run([
            $this->phpBinary(), 'artisan', 'app:deploy', '--force', '--skip-build',
        ]);
        $exitCode = $result->exitCode();

        $log[] = 'Deploy command finished with exit code '.$exitCode;
        Log::info('Deploy webhook: app:deploy output', ['exit_code' => $exitCode, 'output' => $result->output().$result->errorOutput()]);

        return response()->json([
            'success' => $exitCode === 0,
            'log' => $log,
        ], $exitCode === 0 ? 200 : 500);
    }

    /**
     * PHP CLI for app:deploy. The web process may run a CGI build (IONOS:
     * /usr/lib/cgi-bin/php8.5), so prefer the CLI of the same version.
     */
    private function phpBinary(): string
    {
        if ($configured = config('deploy.php_binary')) {
            return $configured;
        }

        $cli = '/usr/bin/php'.PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION.'-cli';

        return is_executable($cli) ? $cli : PHP_BINARY;
    }

    private function extractRelease(string $zipPath): int
    {
        $tempDir = base_path(self::TEMP_DIR);

        // Clean previous temp dir
        if (is_dir($tempDir)) {
            $this->deleteDirectory($tempDir);
        }

        // Extract ZIP
        $zip = new ZipArchive;

        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('Failed to open ZIP archive');
        }

        $zip->extractTo($tempDir);
        $zip->close();

        // Overlay files onto project
        $count = $this->overlayFiles($tempDir, base_path());

        // Cleanup
        $this->deleteDirectory($tempDir);
        unlink($zipPath);

        return $count;
    }

    private function overlayFiles(string $source, string $destination): int
    {
        $count = 0;
        $sourceLen = strlen($source) + 1;

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, \FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
        );

        foreach ($iterator as $item) {
            $relativePath = substr($item->getPathname(), $sourceLen);

            if ($this->isProtected($relativePath)) {
                continue;
            }

            $targetPath = $destination.DIRECTORY_SEPARATOR.$relativePath;

            if ($item->isDir()) {
                if (! is_dir($targetPath)) {
                    mkdir($targetPath, 0755, true);
                }
            } else {
                $dir = dirname($targetPath);
                if (! is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }

                copy($item->getPathname(), $targetPath);
                $count++;
            }
        }

        return $count;
    }

    private function isProtected(string $relativePath): bool
    {
        $normalized = str_replace('\\', '/', $relativePath);

        foreach (self::PROTECTED_PATHS as $protected) {
            if ($normalized === $protected || str_starts_with($normalized, $protected.'/')) {
                return true;
            }
        }

        return false;
    }

    private function deleteDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($iterator as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }

        rmdir($dir);
    }
}
