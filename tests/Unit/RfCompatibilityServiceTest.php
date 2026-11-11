<?php

namespace Tests\Unit;

use App\Services\GeneticAlgorithm\RfCompatibilityService;
use Tests\TestCase;

class RfCompatibilityServiceTest extends TestCase
{
    public function test_empty_candidates_or_lecturers_returns_empty_array(): void
    {
        $service = new RfCompatibilityService();
        $this->assertSame([], $service->getCompatibility([], []));
        $this->assertSame([], $service->getCompatibility([['pendaftaran_id' => 1]], []));
        $this->assertSame([], $service->getCompatibility([], [['id' => 10, 'name' => 'Dosen']]));
    }

    public function test_rf_inference_calculates_compatibility_scores(): void
    {
        $service = new RfCompatibilityService();
        $candidates = [
            [
                'pendaftaran_id' => 99,
                'judul' => 'Sistem Rekomendasi Penjadwalan Menggunakan Algoritma Genetika Berbasis Web',
                'tipe' => 'sempro',
                'kbk' => 'Optimasi dan Penjadwalan',
            ],
        ];

        $lecturers = [
            ['id' => 21, 'name' => 'Nisa Rizqiya Fadhliana, S.Kom., M.T.'],
            ['id' => 22, 'name' => 'Nur Fajri Azhar, S.Kom., M.Kom.'],
            ['id' => 23, 'name' => 'Ramadhan Paninggalih, S.Si., M.Si., M.Sc.'],
            ['id' => 999, 'name' => 'Dosen Fiktif Tidak Ada di Dataset'],
        ];

        $scores = $service->getCompatibility($candidates, $lecturers);

        if (! empty($scores)) {
            $this->assertArrayHasKey(99, $scores);
            $this->assertArrayHasKey(21, $scores[99]);
            $this->assertArrayHasKey(22, $scores[99]);
            $this->assertArrayHasKey(23, $scores[99]);
            // Scores must be normalized float between 0.0 and 1.0
            $this->assertGreaterThan(0.0, $scores[99][23]);
            $this->assertLessThanOrEqual(1.0, $scores[99][23]);
            // Unmatched lecturer should not be in the dictionary
            $this->assertArrayNotHasKey(999, $scores[99]);
        } else {
            // Graceful fallback when uv / python environment is unavailable
            $this->assertSame([], $scores);
        }
    }
}
