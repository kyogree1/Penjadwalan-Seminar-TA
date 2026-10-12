export interface ScheduleSlot {
    tanggal: string;
    jam_mulai: string;
    jam_selesai: string;
}
export interface ScheduleCandidate {
    pendaftaran_id: number;
    mahasiswa_id: number;
    tipe: 'sempro' | 'sidang';
    supervisor_ids: number[];
    nama: string;
    nim: string | null;
    judul: string;
    kbk: string | null;
}
export interface SchedulePublication extends ScheduleSlot {
    pendaftaran_id: number;
    mahasiswa_id: number;
    tipe: 'sempro' | 'sidang';
    penguji_1_id: number;
    penguji_2_id: number;
}
export interface OptimizerResult {
    success: boolean;
    outcome: 'complete' | 'partial' | 'infeasible';
    period_id: number;
    schedule: (ScheduleCandidate & SchedulePublication)[];
    unscheduled: { pendaftaran_id: number; tipe: string }[];
    fitness: number;
    kbk_match: number;
    workload_balance: number;
    generations: number;
    logs: string[];
    limitations: string[];
}
export interface SchedulePeriod {
    id: number;
    name: string;
    tipe: 'sempro' | 'sidang';
    gelombang: number;
    capacity: number;
    start_date: string;
    end_date: string;
    slots: ScheduleSlot[];
    is_open: boolean;
}
export interface PersistedSchedule extends SchedulePublication {
    id: number;
    period_id: number | null;
    status: string;
    mahasiswa: { name: string } | null;
    penguji1: { name: string } | null;
    penguji2: { name: string } | null;
}
