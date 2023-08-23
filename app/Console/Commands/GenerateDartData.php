<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateDartData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-dart-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the optimal target location for a range of standard deviations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $highestExpectedPoints = 0;

        $size = 180;
        $sd = 50; // 50 = 12.8977579863208

        $startBase = now();
        $this->newLine();
        $this->line('Generating base matrices...');

        $dartboardMtx = $this->createDartboardMatrix($size);
        $densityMtx = $this->createDensityMatrix($sd, $size);
        $resultMtx = $this->createEmptyArray($size);

        $this->line('... done. [took: '. now()->diffForHumans($startBase) .']');
        $this->newLine();
        $startCalcTotal = now();
        $this->line('Starting calculation...');
        $this->newLine();

        $dartboardMtxCount = count($dartboardMtx)-1;
        $densityMtxCount = count($densityMtx)-1;
        $densityMtxCountHalf = $densityMtxCount / 2;

        for($di = 0; $di <= $dartboardMtxCount; $di++) {
            $startCalc = now();
            $this->line('calculating... ['. $di .']');
            for($dj = 0; $dj <= $dartboardMtxCount; $dj++) {
                $sum = 0;
                for($i = 0; $i <= $densityMtxCount; $i++)
                    for($j = 0; $j <= $densityMtxCount; $j++) {
                        $x = $j + $dj - $densityMtxCountHalf;
                        $y = $i + $di - $densityMtxCountHalf;
                        if($x < 0 || $y < 0 || $x > $densityMtxCount || $y > $densityMtxCount)
                            continue;
                        $sum += $densityMtx[$i][$j] * $dartboardMtx[$y][$x];
                    }
                $resultMtx[$dj][$di] = $sum;
                if($sum > $highestExpectedPoints)
                    $highestExpectedPoints = $sum;
            }
            $this->line('... done. [took: '. now()->diffForHumans($startCalc) .']');
        }

        $this->line('... done. [took: '. now()->diffForHumans($startCalcTotal) .']');
        $this->newLine();

        $this->line('Output -> ');
        // $this->info($this->getScore(30, 30));
        $this->info('Expected points: '. $highestExpectedPoints);
        $this->newLine();
    }

    /**
     *
     */
    protected function createDartboardMatrix(int $size = 180): array
    {
        $matrix = [];

        for($i = -$size; $i < $size+1; $i++) {
            $mtx = [];
            for($j = -$size; $j < $size+1; $j++)
                $mtx[] = $this->getScore($j, $i);
            $matrix[] = $mtx;
        }

        return $matrix;
    }

    /**
     *
     */
    protected function createDensityMatrix(int $standardDeviation, int $size = 180): array
    {
        $mu = 0;
        $matrix = [];

        for($i = -$size; $i < $size+1; $i++) {
            $mtx = [];
            for($j = -$size; $j < $size+1; $j++) {
                $val1 = $this->probability_density_normal_dist($i, $mu, $standardDeviation);
                $val2 = $this->probability_density_normal_dist($j, $mu, $standardDeviation);
                $mtx[] = $val1 * $val2;
            }
            $matrix[] = $mtx;
        }

        return $matrix;
    }

    /**
     *
     */
    protected function probability_density_normal_dist(float $x, float $mu, float $sigma): float
    {
        $num = exp( -pow(($x - $mu), 2) / (2 * pow($sigma, 2)));
        $denom = $sigma * sqrt(2 * pi());

        return $num / $denom;
    }

    /**
     *
     */
    protected function getScore(float $x, float $y): int
    {
        $fields = [1, 18, 4, 13, 6, 10, 15, 2, 17, 3, 19, 7, 16, 8, 11, 14, 9, 12, 5, 20];
        $rings = [0, 6.4, 16, 99, 107, 162, 170, INF];

        // to polar coordinates
        $r = sqrt($x*$x + $y*$y);
        $theta = 50;
        if($r != 0)
            $theta = rad2deg(atan2($y, $x));

        // determine the ring (50, 25, single, tripple, single, double, out)
        // $ring = array_filter($rings,function($ring) use ($r) { return $ring >= $r; }); // keep for perf comp later
        $ringIndex = 0;
        foreach($rings as $i => $ring)
            if($ring >= $r) {
                $ringIndex = $i == 0 ? 0 : $i-1;
                break;
            }

        // determine the field
        $fieldSize = 360 / count($fields);
        // align field "1" with 0 deg
        $phi = $theta - ($fieldSize / 2) + 360;
        $phi = $phi % 360;
        $field = 0;
        foreach($fields as $i => $f)
            if($fieldSize * $i >= $phi) {
                $field = $f;
                break;
            }

        $points = [50, 25, $field, $field * 3, $field, $field * 2, 0];
        return $points[$ringIndex];
    }

    /**
     *
     */
    private function createEmptyArray(int $size): array
    {
        $matrix = [];
        $s = $size * 2 + 1;
        for($i = 0; $i < $s; $i++)
            $matrix[$i] = array_fill(0, $s, 0);

        return $matrix;
    }
}
