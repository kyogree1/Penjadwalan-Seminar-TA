<?php

namespace App\Services\GeneticAlgorithm;

/** Tournament selection k=3; two-point crossover; integer random-reset mutation.
 * Hard constraints are enforced by decoding, not finite fitness penalties.
 */
class ScheduleOptimizer
{
    public function __construct(protected int $populationSize = 50, protected int $maxGenerations = 100, protected float $crossoverRate = .8, protected float $mutationRate = .03) {}

    public function optimize(array $candidates, array $lecturers, array $availableSlots, array $bookings = []): array
    {
        $best = [];
        $score = -1;
        $generations = 0;
        $population = [];
        if ($candidates && count($lecturers) >= 2 && $availableSlots) {
            for ($i = 0; $i < $this->populationSize; $i++) {
                $population[] = array_map(fn () => [mt_rand(0, count($availableSlots) - 1), mt_rand(0, count($lecturers) - 1), mt_rand(0, count($lecturers) - 1)], $candidates);
            }
            for ($generations = 1; $generations <= $this->maxGenerations; $generations++) {
                $scores = [];
                foreach ($population as $i => $genes) {
                    $rows = $this->decode($genes, $candidates, $lecturers, $availableSlots, $bookings);
                    $scores[$i] = count($rows) + $this->metrics($rows, $lecturers)['fitness'] / 2;
                    if ($scores[$i] > $score) {
                        $score = $scores[$i];
                        $best = $rows;
                        $elite = $genes;
                    }
                }
                $next = [$elite];
                while (count($next) < $this->populationSize) {
                    $a = $this->select($population, $scores);
                    $b = $this->select($population, $scores);
                    if (count($a) > 1 && mt_rand() / mt_getrandmax() < $this->crossoverRate) {
                        $start = mt_rand(0, count($a) - 2);
                        $end = mt_rand($start + 1, count($a) - 1);
                        for ($j = $start; $j <= $end; $j++) {
                            $a[$j] = $b[$j];
                        }
                    }
                    foreach ($a as &$gene) {
                        foreach ($gene as $field => &$value) {
                            if (mt_rand() / mt_getrandmax() < $this->mutationRate) {
                                $value = mt_rand(0, ($field === 0 ? count($availableSlots) : count($lecturers)) - 1);
                            }
                        }unset($value);
                    }unset($gene);
                    $next[] = $a;
                }
                $population = $next;
            }
            $generations = $this->maxGenerations;
        }
        $keys = array_map(fn ($r) => $r['tipe'].':'.$r['pendaftaran_id'], $best);
        $unscheduled = array_values(array_filter($candidates, fn ($r) => ! in_array($r['tipe'].':'.$r['pendaftaran_id'], $keys, true)));
        $outcome = ! $best ? 'infeasible' : ($unscheduled ? 'partial' : 'complete');

        return array_merge($this->metrics($best, $lecturers), ['success' => $outcome === 'complete', 'outcome' => $outcome, 'generations' => $generations, 'schedule' => $best, 'unscheduled' => $unscheduled, 'logs' => [count($best).'/'.count($candidates).' dijadwalkan; '.$outcome.'. Pencarian terbatas, bukan bukti optimalitas global.']]);
    }

    private function select(array $population, array $scores): array
    {
        $winner = array_rand($population);
        for ($i = 1; $i < 3; $i++) {
            $j = array_rand($population);
            if ($scores[$j] > $scores[$winner]) {
                $winner = $j;
            }
        }

        return $population[$winner];
    }

    private function decode(array $genes, array $candidates, array $lecturers, array $slots, array $bookings): array
    {
        $rows = [];
        $loads = [];
        foreach ($bookings as $booking) {
            foreach (['penguji_1_id', 'penguji_2_id'] as $field) {
                if (! empty($booking[$field])) {
                    $loads[$booking[$field]] = ($loads[$booking[$field]] ?? 0) + 1;
                }
            }
        }
        foreach ($candidates as $index => $candidate) {
            [$offset,$first,$second] = $genes[$index];
            $found = false;
            for ($s = 0; $s < count($slots) && ! $found; $s++) {
                $slot = $slots[($offset + $s) % count($slots)];
                for ($p = 0; $p < count($lecturers) && ! $found; $p++) {
                    for ($q = 0; $q < count($lecturers) && ! $found; $q++) {
                        $a = $lecturers[($first + $p) % count($lecturers)];
                        $b = $lecturers[($second + $q) % count($lecturers)];
                        $row = array_merge($candidate, $slot, ['penguji_1_id' => $a['id'], 'penguji_2_id' => $b['id']]);
                        if (! $this->conflicts($row, array_merge($bookings, $rows)) && $this->available($a, $slot, $loads) && $this->available($b, $slot, $loads)) {
                            $rows[] = $row;
                            $loads[$a['id']] = ($loads[$a['id']] ?? 0) + 1;
                            $loads[$b['id']] = ($loads[$b['id']] ?? 0) + 1;
                            $found = true;
                        }
                    }
                }
            }
        }

        return $rows;
    }

    private function available(array $lecturer, array $slot, array $loads): bool
    {
        if (isset($lecturer['capacity']) && ($loads[$lecturer['id']] ?? 0) >= $lecturer['capacity']) {
            return false;
        }
        if (! isset($lecturer['availability'])) {
            return true;
        }
        foreach ($lecturer['availability'] as $window) {
            if ($window['tanggal'] === $slot['tanggal'] && $window['jam_mulai'] <= $slot['jam_mulai'] && $window['jam_selesai'] >= $slot['jam_selesai']) {
                return true;
            }
        }

        return false;
    }

    public function conflicts(array $row, array $bookings): bool
    {
        $examiners = [$row['penguji_1_id'], $row['penguji_2_id']];
        $supervisors = $row['supervisor_ids'] ?? array_values(array_filter([$row['pembimbing_1_id'] ?? null, $row['pembimbing_2_id'] ?? null]));
        if ($examiners[0] === $examiners[1] || array_intersect($examiners, $supervisors) || $row['jam_mulai'] >= $row['jam_selesai']) {
            return true;
        }
        $participants = array_merge($examiners, $supervisors, [$row['mahasiswa_id']]);
        foreach ($bookings as $booking) {
            $others = array_merge(array_filter([$booking['penguji_1_id'] ?? null, $booking['penguji_2_id'] ?? null, $booking['pembimbing_1_id'] ?? null, $booking['pembimbing_2_id'] ?? null, $booking['mahasiswa_id'] ?? null]), $booking['supervisor_ids'] ?? []);
            if ($booking['tanggal'] === $row['tanggal'] && $booking['jam_mulai'] < $row['jam_selesai'] && $row['jam_mulai'] < $booking['jam_selesai'] && array_intersect($participants, $others)) {
                return true;
            }
        }

        return false;
    }

    public function metrics(array $rows, array $lecturers): array
    {
        $map = array_column($lecturers, null, 'id');
        $loads = array_map(fn ($l) => $l['workload'] ?? 0, $map);
        $matches = 0;
        foreach ($rows as $row) {
            foreach (['penguji_1_id', 'penguji_2_id'] as $field) {
                $id = $row[$field];
                $loads[$id] = ($loads[$id] ?? 0) + 1;
                if (! empty($row['kbk']) && ($map[$id]['kbk'] ?? null) === $row['kbk']) {
                    $matches++;
                }
            }
        }
        $match = $rows ? $matches / (2 * count($rows)) : 0;
        $balance = $loads ? 1 / (1 + max($loads) - min($loads)) : 0;

        return ['fitness' => $rows ? round(.5 * $match + .5 * $balance,6) : 0, 'kbk_match' => $match, 'workload_balance' => $balance, 'workloads' => $loads];
    }
}
