<?php

namespace Tests\Unit;

use App\Services\GeneticAlgorithm\ScheduleOptimizer;
use PHPUnit\Framework\TestCase;

class ScheduleOptimizerTest extends TestCase
{
    public function test_ga_optimizer_converges_and_produces_schedule(): void
    {
        $optimizer = new ScheduleOptimizer(
            populationSize: 20,
            maxGenerations: 30,
            crossoverRate: 0.85,
            mutationRate: 0.03
        );

        $candidates = [
            [
                'id' => 1,
                'nama' => 'Muhammad Raihan',
                'nim' => '11211030',
                'judul' => 'Analisis Kerentanan Web',
                'kbk' => 'Cybersecurity',
                'supervisors' => [10],
            ],
            [
                'id' => 2,
                'nama' => 'Siti Rahmawati',
                'nim' => '11221068',
                'judul' => 'Peramalan Penjualan Harian',
                'kbk' => 'AI & Data Science',
                'supervisors' => [11],
            ],
        ];

        $lecturers = [
            ['id' => 10, 'name' => 'Dosen A'],
            ['id' => 11, 'name' => 'Dosen B'],
            ['id' => 12, 'name' => 'Dosen C'],
            ['id' => 13, 'name' => 'Dosen D'],
        ];

        $slots = [
            ['tanggal' => '2026-09-28', 'jam_mulai' => '09:00:00', 'jam_selesai' => '10:30:00'],
            ['tanggal' => '2026-09-28', 'jam_mulai' => '10:45:00', 'jam_selesai' => '12:15:00'],
            ['tanggal' => '2026-09-29', 'jam_mulai' => '09:00:00', 'jam_selesai' => '10:30:00'],
        ];

        $result = $optimizer->optimize($candidates, $lecturers, $slots);

        $this->assertTrue($result['success']);
        $this->assertGreaterThan(0.0, $result['fitness']);
        $this->assertCount(2, $result['schedule']);
        $this->assertNotEmpty($result['logs']);
    }

    public function test_ga_optimizer_handles_empty_inputs_gracefully(): void
    {
        $optimizer = new ScheduleOptimizer();
        $result = $optimizer->optimize([], [], []);

        $this->assertFalse($result['success']);
        $this->assertEquals(0.0, $result['fitness']);
        $this->assertEmpty($result['schedule']);
    }
}
