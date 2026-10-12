<?php

namespace Tests\Unit;

use App\Services\GeneticAlgorithm\ScheduleOptimizer;
use PHPUnit\Framework\TestCase;

class R4OptimizerTest extends TestCase
{
    private function candidate(int $id = 1): array
    {
        return ['pendaftaran_id' => $id, 'mahasiswa_id' => $id + 100, 'tipe' => 'sempro', 'supervisor_ids' => [10], 'kbk' => 'AI'];
    }

    private function slots(): array
    {
        return [
            ['tanggal' => '2026-11-01', 'jam_mulai' => '09:00:00', 'jam_selesai' => '10:30:00'],
            ['tanggal' => '2026-11-01', 'jam_mulai' => '10:00:00', 'jam_selesai' => '11:00:00'],
        ];
    }

    public function test_infeasible_supervisors_and_capacity_are_not_success(): void
    {
        mt_srand(41);
        $result = (new ScheduleOptimizer(10, 10))->optimize([$this->candidate()], [['id' => 10], ['id' => 11]], $this->slots());
        $this->assertFalse($result['success']);
        $this->assertSame('infeasible', $result['outcome']);
        $this->assertEmpty($result['schedule']);
        $this->assertCount(1, $result['unscheduled']);
    }

    public function test_overlapping_intervals_and_existing_bookings_are_excluded(): void
    {
        mt_srand(41);
        $optimizer = new ScheduleOptimizer(10, 10);
        $lecturers = [['id' => 11], ['id' => 12]];
        $result = $optimizer->optimize([$this->candidate(), $this->candidate(2)], $lecturers, $this->slots());
        $this->assertSame('partial', $result['outcome']);
        $this->assertCount(1, $result['schedule']);
        $row = $result['schedule'][0];
        $this->assertSame(101, $row['mahasiswa_id']);
        $this->assertSame(1, $row['pendaftaran_id']);
        $this->assertSame('sempro', $row['tipe']);
        $this->assertNotSame($row['penguji_1_id'], $row['penguji_2_id']);
        $booked = array_merge($this->slots()[0], ['mahasiswa_id' => 200, 'penguji_1_id' => 11, 'penguji_2_id' => 12]);
        $result = $optimizer->optimize([$this->candidate()], $lecturers, $this->slots(), [$booked]);
        $this->assertSame('infeasible', $result['outcome']);
    }

    public function test_availability_capacity_and_seed_reproducibility(): void
    {
        $optimizer = new ScheduleOptimizer(10, 10, 1, 1);
        $lecturers = [['id' => 11, 'kbk' => 'AI', 'capacity' => 1, 'availability' => [$this->slots()[0]]], ['id' => 12, 'kbk' => 'AI', 'capacity' => 1, 'availability' => [$this->slots()[0]]]];
        mt_srand(101);
        $first = $optimizer->optimize([$this->candidate(), $this->candidate(2)], $lecturers, $this->slots());
        mt_srand(101);
        $second = $optimizer->optimize([$this->candidate(), $this->candidate(2)], $lecturers, $this->slots());
        $this->assertSame($first, $second);
        $this->assertSame('partial', $first['outcome']);
        $this->assertCount(1, $first['schedule']);
        $lecturers[0]['availability'] = [];
        $this->assertSame('infeasible', $optimizer->optimize([$this->candidate()], $lecturers, $this->slots())['outcome']);
    }

    public function test_kbk_and_workload_actually_change_fitness(): void
    {
        $optimizer = new ScheduleOptimizer;
        $rows = [array_merge($this->candidate(), $this->slots()[0], ['penguji_1_id' => 11, 'penguji_2_id' => 12])];
        $good = $optimizer->metrics($rows, [['id' => 11, 'kbk' => 'AI'], ['id' => 12, 'kbk' => 'AI']]);
        $bad = $optimizer->metrics($rows, [['id' => 11, 'kbk' => 'Web'], ['id' => 12, 'kbk' => 'Web']]);
        $this->assertGreaterThan($bad['fitness'], $good['fitness']);
        $unequal = $optimizer->metrics($rows, [['id' => 11, 'kbk' => 'AI', 'workload' => 10], ['id' => 12, 'kbk' => 'AI']]);
        $this->assertGreaterThan($unequal['fitness'], $good['fitness']);
    }
}
