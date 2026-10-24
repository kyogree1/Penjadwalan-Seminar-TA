<?php

namespace App\Services\GeneticAlgorithm;

class ScheduleOptimizer
{
    public const NEUTRAL_SCORE = .5;

    public const PENALTY = 1.0;

    public function __construct(protected int $populationSize = 50, protected int $maxGenerations = 100, protected float $crossoverRate = .8, protected float $mutationRate = .03, protected int $stagnationLimit = 20, protected float $repairRate = 1.0) {}

    public function optimize(array $candidates, array $lecturers, array $availableSlots, array $bookings = [], array $compatibility = []): array
    {
        $lecturers = array_values($lecturers);
        $slots = array_values($availableSlots);
        $lecturerMap = array_column($lecturers, null, 'id');
        $booked = $this->indexByDate($bookings);
        $overlaps = [];
        $bookedBusy = [];
        foreach ($slots as $s => $slot) {
            $bookedBusy[$s] = [];
            foreach ($slots as $t => $other) {
                if ($other['tanggal'] === $slot['tanggal'] && $other['jam_mulai'] < $slot['jam_selesai'] && $slot['jam_mulai'] < $other['jam_selesai']) {
                    $overlaps[$s][] = $t;
                }
            }
            foreach ($booked[$slot['tanggal']] ?? [] as [$start, $end, $people]) {
                if ($start < $slot['jam_selesai'] && $slot['jam_mulai'] < $end) {
                    foreach ($people as $id) {
                        $bookedBusy[$s][$id] = true;
                    }
                }
            }
        }
        $schedulable = [];
        $pools = [];
        foreach (array_values($candidates) as $candidate) {
            $supervisors = $this->supervisors($candidate);
            $pool = array_keys(array_filter($lecturers, fn ($lecturer) => ! in_array($lecturer['id'], $supervisors)));
            if ($slots && count($pool) >= 2) {
                $schedulable[] = $candidate;
                $pools[] = $pool;
            }
        }

        $best = null;
        $history = [];
        $logs = [];
        $generations = 0;
        $stopReason = 'no_candidates';
        if ($schedulable) {
            $logs[] = 'Inisialisasi populasi acak: '.$this->populationSize.' kromosom, '.count($schedulable).' kandidat, '.count($lecturers).' dosen, '.count($slots).' slot.';
            $population = [];
            for ($i = 0; $i < $this->populationSize; $i++) {
                $population[] = array_map(fn ($pool) => [mt_rand(0, count($slots) - 1), mt_rand(0, count($pool) - 1), mt_rand(0, count($pool) - 2)], $pools);
            }
            $stale = 0;
            for ($generations = 1; $generations <= $this->maxGenerations; $generations++) {
                $scores = [];
                $improved = false;
                foreach ($population as $i => $genes) {
                    $result = $this->score($this->decode($genes, $schedulable, $pools, $lecturers, $slots), $lecturerMap, $booked, $compatibility);
                    if ($result['bad'] && $this->repairRate > 0) {
                        $genes = $this->repair($genes, $result['bad'], $schedulable, $pools, $lecturers, $lecturerMap, $slots, $overlaps, $bookedBusy);
                        $population[$i] = $genes;
                        $result = $this->score($this->decode($genes, $schedulable, $pools, $lecturers, $slots), $lecturerMap, $booked, $compatibility);
                    }
                    $scores[$i] = $result['fitness'];
                    if (! $best || $result['violations'] < $best['violations'] || ($result['violations'] === $best['violations'] && $result['fitness'] > $best['fitness'])) {
                        $best = $result + ['genes' => $genes];
                        $improved = true;
                    }
                }
                $stale = $improved ? 0 : $stale + 1;
                $average = round(array_sum($scores) / count($scores), 6);
                $history[] = ['generation' => $generations, 'best' => $best['fitness'], 'average' => $average, 'violations' => $best['violations']];
                if ($generations === 1 || $generations % 10 === 0) {
                    $logs[] = "Generasi {$generations}: fitness terbaik {$best['fitness']} | rata-rata {$average} | pelanggaran {$best['violations']}.";
                }
                if ($best['violations'] === 0 && $best['fitness'] >= 1.0) {
                    $stopReason = 'optimal';
                    break;
                }
                if ($stale >= $this->stagnationLimit) {
                    $stopReason = 'converged';
                    break;
                }
                if ($generations === $this->maxGenerations) {
                    $stopReason = 'max_generations';
                    break;
                }
                $population = $this->nextGeneration($population, $scores, $best['genes'], count($slots), $pools);
            }
            $logs[] = match ($stopReason) {
                'optimal' => "Berhenti pada generasi {$generations}: fitness optimal 1.0 tercapai.",
                'converged' => "Berhenti pada generasi {$generations}: konvergen, fitness tidak membaik selama {$this->stagnationLimit} generasi.",
                default => "Berhenti pada generasi {$generations}: batas generasi tercapai.",
            };
        }

        $schedule = [];
        if ($best) {
            $loads = array_map(fn ($lecturer) => $lecturer['workload'] ?? 0, $lecturerMap);
            foreach ($this->decode($best['genes'], $schedulable, $pools, $lecturers, $slots) as $row) {
                $examiners = array_unique([$row['penguji_1_id'], $row['penguji_2_id']]);
                $fits = ! $this->conflicts($row, array_merge($bookings, $schedule));
                foreach ($examiners as $id) {
                    $lecturer = $lecturerMap[$id];
                    $fits = $fits && $this->available($lecturer, $row) && (! isset($lecturer['capacity']) || $loads[$id] < $lecturer['capacity']);
                }
                if ($fits) {
                    $schedule[] = $row;
                    foreach ($examiners as $id) {
                        $loads[$id]++;
                    }
                }
            }
        }
        $keys = array_map(fn ($row) => $row['tipe'].':'.$row['pendaftaran_id'], $schedule);
        $unscheduled = array_values(array_filter($candidates, fn ($row) => ! in_array($row['tipe'].':'.$row['pendaftaran_id'], $keys, true)));
        $outcome = ! $schedule ? 'infeasible' : ($unscheduled ? 'partial' : 'complete');
        $final = $this->score($schedule, $lecturerMap, $booked, $compatibility);
        $logs[] = count($schedule).'/'.count($candidates).' dijadwalkan; '.$outcome.'. Pencarian heuristik, bukan bukti optimalitas global.';

        return [
            'fitness' => $best ? $best['fitness'] : 0.0,
            'kbk_match' => round($final['compatibility'], 6),
            'workload_balance' => $final['workload_balance'],
            'workloads' => $final['workloads'],
            'violations' => $best ? $best['violations'] : 0,
            'success' => $outcome === 'complete',
            'outcome' => $outcome,
            'generations' => $generations,
            'stop_reason' => $stopReason,
            'history' => $history,
            'schedule' => $schedule,
            'unscheduled' => $unscheduled,
            'logs' => $logs,
        ];
    }

    public function evaluateRows(array $rows, array $lecturers, array $bookings = [], array $compatibility = []): array
    {
        return $this->score($rows, array_column($lecturers, null, 'id'), $this->indexByDate($bookings), $compatibility);
    }

    public function conflicts(array $row, array $bookings): bool
    {
        $examiners = [$row['penguji_1_id'], $row['penguji_2_id']];
        $supervisors = $this->supervisors($row);
        if ($examiners[0] === $examiners[1] || array_intersect($examiners, $supervisors) || $row['jam_mulai'] >= $row['jam_selesai']) {
            return true;
        }
        $participants = array_merge($examiners, $supervisors, [$row['mahasiswa_id']]);
        foreach ($bookings as $booking) {
            if ($booking['tanggal'] === $row['tanggal'] && $booking['jam_mulai'] < $row['jam_selesai'] && $row['jam_mulai'] < $booking['jam_selesai'] && array_intersect($participants, $this->participants($booking))) {
                return true;
            }
        }

        return false;
    }

    private function score(array $rows, array $lecturerMap, array $booked, array $compatibility): array
    {
        $loads = array_map(fn ($lecturer) => $lecturer['workload'] ?? 0, $lecturerMap);
        $violations = 0;
        $total = 0.0;
        $bad = [];
        foreach ($rows as $index => $row) {
            $before = $violations;
            $examiners = [$row['penguji_1_id'], $row['penguji_2_id']];
            $supervisors = $this->supervisors($row);
            $participants = array_merge($examiners, $supervisors, [$row['mahasiswa_id']]);
            $violations += (int) ($examiners[0] === $examiners[1]) + (int) (bool) array_intersect($examiners, $supervisors) + (int) ($row['jam_mulai'] >= $row['jam_selesai']);
            foreach ($booked[$row['tanggal']] ?? [] as [$start, $end, $others]) {
                if ($start < $row['jam_selesai'] && $row['jam_mulai'] < $end && array_intersect($participants, $others)) {
                    $violations++;
                    break;
                }
            }
            foreach (array_unique($examiners) as $id) {
                $lecturer = $lecturerMap[$id] ?? [];
                $violations += (int) ! $this->available($lecturer, $row) + (int) (isset($lecturer['capacity']) && ($loads[$id] ?? 0) >= $lecturer['capacity']);
                $loads[$id] = ($loads[$id] ?? 0) + 1;
            }
            $booked[$row['tanggal']][] = [$row['jam_mulai'], $row['jam_selesai'], $participants];
            $total += $compatibility[$row['pendaftaran_id']][$row['penguji_1_id']] ?? self::NEUTRAL_SCORE;
            if ($violations > $before) {
                $bad[] = $index;
            }
        }
        $compat = $rows ? $total / count($rows) : 0.0;

        return [
            'fitness' => $rows ? round($compat - self::PENALTY * $violations, 6) : 0.0,
            'compatibility' => $compat,
            'violations' => $violations,
            'bad' => $bad,
            'workloads' => $loads,
            'workload_balance' => $loads ? 1 / (1 + max($loads) - min($loads)) : 0,
        ];
    }

    private function repair(array $genes, array $bad, array $candidates, array $pools, array $lecturers, array $lecturerMap, array $slots, array $overlaps, array $bookedBusy): array
    {
        $rows = $this->decode($genes, $candidates, $pools, $lecturers, $slots);
        $badSet = array_flip($bad);
        $busy = array_fill(0, count($slots), []);
        $loads = array_map(fn ($lecturer) => $lecturer['workload'] ?? 0, $lecturerMap);
        foreach ($rows as $index => $row) {
            if (! isset($badSet[$index])) {
                foreach (array_merge([$row['mahasiswa_id'], $row['penguji_1_id'], $row['penguji_2_id']], $this->supervisors($row)) as $id) {
                    $busy[$genes[$index][0]][$id] = true;
                }
                $loads[$row['penguji_1_id']]++;
                $loads[$row['penguji_2_id']]++;
            }
        }
        foreach ($bad as $index) {
            if (! $this->chance($this->repairRate)) {
                continue;
            }
            $pool = $pools[$index];
            [, $first, $second] = $genes[$index];
            $current = [$pool[$first], $pool[$second < $first ? $second : $second + 1]];
            $fixed = array_merge([$candidates[$index]['mahasiswa_id']], $this->supervisors($candidates[$index]));
            $offset = mt_rand(0, count($slots) - 1);
            $choice = null;
            foreach ([2, 1, 0] as $kept) {
                for ($k = 0; $k < count($slots) && ! $choice; $k++) {
                    $s = ($offset + $k) % count($slots);
                    if (! $this->free($fixed, $s, $busy, $overlaps, $bookedBusy)) {
                        continue;
                    }
                    $free = array_values(array_filter($pool, function ($li) use ($lecturers, $slots, $s, $busy, $overlaps, $bookedBusy, $loads) {
                        $lecturer = $lecturers[$li];

                        return $this->free([$lecturer['id']], $s, $busy, $overlaps, $bookedBusy) && $this->available($lecturer, $slots[$s]) && (! isset($lecturer['capacity']) || $loads[$lecturer['id']] < $lecturer['capacity']);
                    }));
                    $keep = array_slice($current, 0, $kept);
                    if (array_diff($keep, $free) || count($free) < 2) {
                        continue;
                    }
                    $rest = array_values(array_diff($free, $keep));
                    while (count($keep) < 2) {
                        $pick = mt_rand(0, count($rest) - 1);
                        $keep[] = $rest[$pick];
                        array_splice($rest, $pick, 1);
                    }
                    $choice = [$s, $keep];
                }
                if ($choice) {
                    break;
                }
            }
            if ($choice) {
                [$s, [$a, $b]] = $choice;
                $p1 = array_search($a, $pool, true);
                $p2 = array_search($b, $pool, true);
                $genes[$index] = [$s, $p1, $p2 < $p1 ? $p2 : $p2 - 1];
                foreach (array_merge($fixed, [$lecturers[$a]['id'], $lecturers[$b]['id']]) as $id) {
                    $busy[$s][$id] = true;
                }
                $loads[$lecturers[$a]['id']]++;
                $loads[$lecturers[$b]['id']]++;
            }
        }

        return $genes;
    }

    private function free(array $people, int $slot, array $busy, array $overlaps, array $bookedBusy): bool
    {
        foreach ($people as $id) {
            if (isset($bookedBusy[$slot][$id])) {
                return false;
            }
            foreach ($overlaps[$slot] as $other) {
                if (isset($busy[$other][$id])) {
                    return false;
                }
            }
        }

        return true;
    }

    private function decode(array $genes, array $candidates, array $pools, array $lecturers, array $slots): array
    {
        $rows = [];
        foreach ($candidates as $index => $candidate) {
            [$slot, $first, $second] = $genes[$index];
            $pool = $pools[$index];
            $rows[] = array_merge($candidate, $slots[$slot], ['penguji_1_id' => $lecturers[$pool[$first]]['id'], 'penguji_2_id' => $lecturers[$pool[$second < $first ? $second : $second + 1]]['id']]);
        }

        return $rows;
    }

    private function nextGeneration(array $population, array $scores, array $elite, int $slotCount, array $pools): array
    {
        $minimum = min($scores);
        $weights = array_map(fn ($score) => $score - $minimum + 1e-6, $scores);
        $total = array_sum($weights);
        $next = [$elite];
        while (count($next) < $this->populationSize) {
            $a = $population[$this->spin($weights, $total)];
            $b = $population[$this->spin($weights, $total)];
            if (count($a) > 1 && $this->chance($this->crossoverRate)) {
                $start = mt_rand(0, count($a) - 2);
                $end = mt_rand($start + 1, count($a) - 1);
                for ($j = $start; $j <= $end; $j++) {
                    $a[$j] = $b[$j];
                }
            }
            foreach ($a as $index => &$gene) {
                $limits = [$slotCount, count($pools[$index]), count($pools[$index]) - 1];
                foreach ($gene as $field => &$value) {
                    if ($this->chance($this->mutationRate)) {
                        $value = mt_rand(0, $limits[$field] - 1);
                    }
                }
                unset($value);
            }
            unset($gene);
            $next[] = $a;
        }

        return $next;
    }

    private function spin(array $weights, float $total): int
    {
        $target = mt_rand() / mt_getrandmax() * $total;
        $weights = array_values($weights);
        foreach ($weights as $index => $weight) {
            $target -= $weight;
            if ($target <= 0) {
                return $index;
            }
        }

        return count($weights) - 1;
    }

    private function chance(float $rate): bool
    {
        return mt_rand() / mt_getrandmax() < $rate;
    }

    private function available(array $lecturer, array $slot): bool
    {
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

    private function supervisors(array $row): array
    {
        return $row['supervisor_ids'] ?? array_values(array_filter([$row['pembimbing_1_id'] ?? null, $row['pembimbing_2_id'] ?? null]));
    }

    private function participants(array $booking): array
    {
        return array_merge(array_filter([$booking['penguji_1_id'] ?? null, $booking['penguji_2_id'] ?? null, $booking['pembimbing_1_id'] ?? null, $booking['pembimbing_2_id'] ?? null, $booking['mahasiswa_id'] ?? null]), $booking['supervisor_ids'] ?? []);
    }

    private function indexByDate(array $bookings): array
    {
        $index = [];
        foreach ($bookings as $booking) {
            $index[$booking['tanggal']][] = [$booking['jam_mulai'], $booking['jam_selesai'], $this->participants($booking)];
        }

        return $index;
    }
}
