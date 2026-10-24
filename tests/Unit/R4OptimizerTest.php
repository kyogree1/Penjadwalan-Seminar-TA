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

    public function test_compatibility_raises_and_constraint_penalties_lower_fitness(): void
    {
        $optimizer = new ScheduleOptimizer;
        $rows = [array_merge($this->candidate(), $this->slots()[0], ['penguji_1_id' => 11, 'penguji_2_id' => 12])];
        $lecturers = [['id' => 11], ['id' => 12]];
        $good = $optimizer->evaluateRows($rows, $lecturers, [], [1 => [11 => .9]]);
        $bad = $optimizer->evaluateRows($rows, $lecturers, [], [1 => [11 => .1]]);
        $this->assertGreaterThan($bad['fitness'], $good['fitness']);
        $this->assertSame(ScheduleOptimizer::NEUTRAL_SCORE, $optimizer->evaluateRows($rows, $lecturers)['fitness']);
        $overQuota = $optimizer->evaluateRows($rows, [['id' => 11, 'capacity' => 1, 'workload' => 1], ['id' => 12]], [], [1 => [11 => .9]]);
        $this->assertSame(1, $overQuota['violations']);
        $this->assertLessThan(0, $overQuota['fitness']);
        $clash = $optimizer->evaluateRows($rows, $lecturers, [array_merge($this->slots()[0], ['mahasiswa_id' => 200, 'penguji_1_id' => 11, 'penguji_2_id' => 13])]);
        $this->assertSame(1, $clash['violations']);
    }

    public function test_compatibility_steers_first_examiner_and_stops_at_optimum(): void
    {
        mt_srand(7);
        $result = (new ScheduleOptimizer(20, 50, .8, .3))->optimize([$this->candidate()], [['id' => 11], ['id' => 12], ['id' => 13]], [$this->slots()[0]], [], [1 => [11 => 0.0, 12 => 0.0, 13 => 1.0]]);
        $this->assertSame('complete', $result['outcome']);
        $this->assertSame(13, $result['schedule'][0]['penguji_1_id']);
        $this->assertSame('optimal', $result['stop_reason']);
        $this->assertSame(1.0, $result['fitness']);
        $this->assertLessThan(50, $result['generations']);
    }

    public function test_repair_operator_completes_dense_schedule(): void
    {
        $candidates = [];
        for ($i = 1; $i <= 16; $i++) {
            $candidates[] = ['pendaftaran_id' => $i, 'mahasiswa_id' => 100 + $i, 'tipe' => 'sidang', 'supervisor_ids' => [$i % 8 + 1, ($i + 3) % 8 + 1]];
        }
        $lecturers = array_map(fn ($id) => ['id' => $id, 'capacity' => 5], range(1, 8));
        $slots = [];
        foreach (['2026-11-02', '2026-11-03', '2026-11-04', '2026-11-05'] as $day) {
            foreach ([['08:00:00', '09:30:00'], ['10:00:00', '11:30:00'], ['13:00:00', '14:30:00']] as [$start, $end]) {
                $slots[] = ['tanggal' => $day, 'jam_mulai' => $start, 'jam_selesai' => $end];
            }
        }
        mt_srand(11);
        $repaired = (new ScheduleOptimizer(20, 50))->optimize($candidates, $lecturers, $slots);
        $this->assertSame('complete', $repaired['outcome']);
        $this->assertSame(0, $repaired['violations']);
        mt_srand(11);
        $penaltyOnly = (new ScheduleOptimizer(20, 50, .8, .03, 20, 0.0))->optimize($candidates, $lecturers, $slots);
        $this->assertLessThan(count($repaired['schedule']) + 1, count($penaltyOnly['schedule']));
    }

    public function test_history_tracks_non_decreasing_best_until_convergence(): void
    {
        mt_srand(3);
        $slots = [
            ['tanggal' => '2026-11-01', 'jam_mulai' => '09:00:00', 'jam_selesai' => '10:30:00'],
            ['tanggal' => '2026-11-02', 'jam_mulai' => '09:00:00', 'jam_selesai' => '10:30:00'],
        ];
        $result = (new ScheduleOptimizer(10, 100, .8, .03, 5))->optimize([$this->candidate(), $this->candidate(2)], [['id' => 11], ['id' => 12], ['id' => 13]], $slots);
        $this->assertSame('converged', $result['stop_reason']);
        $this->assertLessThan(100, $result['generations']);
        $this->assertCount($result['generations'], $result['history']);
        $best = array_column($result['history'], 'best');
        for ($i = 1; $i < count($best); $i++) {
            $this->assertGreaterThanOrEqual($best[$i - 1], $best[$i]);
        }
    }
}
