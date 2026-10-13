<script setup lang="ts">
defineProps<{
    schedules: {
        student_name: string | null;
        title: string | null;
        type: 'sempro' | 'sidang';
        date: string;
        start_time: string;
        end_time: string;
        room: string | null;
        mode: 'offline' | 'online' | 'hybrid';
        supervisors: string[];
        examiners: string[];
    }[];
}>();
</script>

<template>
    <section
        class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8"
        aria-labelledby="public-schedules-heading"
    >
        <h2 id="public-schedules-heading" class="text-lg font-semibold">
            Jadwal Seminar &amp; Sidang
        </h2>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
            Jadwal terpublikasi dapat dilihat tanpa masuk ke portal.
        </p>
        <p
            v-if="schedules.length === 0"
            class="mt-4 rounded-xl border border-slate-200 bg-white p-4 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"
        >
            Belum ada jadwal terpublikasi.
        </p>
        <ul v-else class="mt-4 grid gap-4 md:grid-cols-2">
            <li
                v-for="(schedule, index) in schedules"
                :key="index"
                class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900"
            >
                <p
                    class="text-sm font-semibold text-blue-700 dark:text-blue-300"
                >
                    {{
                        schedule.type === 'sempro'
                            ? 'Seminar Proposal'
                            : 'Sidang'
                    }}
                </p>
                <h3 class="mt-2 font-semibold">
                    {{
                        schedule.student_name ?? 'Nama mahasiswa tidak tersedia'
                    }}
                </h3>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                    {{ schedule.title ?? 'Judul tidak tersedia' }}
                </p>
                <dl class="mt-4 space-y-2 text-sm">
                    <div>
                        <dt class="font-medium">Tanggal &amp; waktu</dt>
                        <dd>
                            <time :datetime="schedule.date">{{
                                schedule.date
                            }}</time>
                            · {{ schedule.start_time.slice(0, 5) }}–{{
                                schedule.end_time.slice(0, 5)
                            }}
                        </dd>
                    </div>
                    <div>
                        <dt class="font-medium">Pelaksanaan</dt>
                        <dd>
                            {{
                                schedule.mode === 'online'
                                    ? 'Online'
                                    : schedule.mode === 'hybrid'
                                      ? 'Hybrid'
                                      : 'Offline'
                            }}
                            · {{ schedule.room ?? 'Ruangan tidak tersedia' }}
                        </dd>
                    </div>
                    <div v-if="schedule.supervisors.length">
                        <dt class="font-medium">Pembimbing</dt>
                        <dd>{{ schedule.supervisors.join(', ') }}</dd>
                    </div>
                    <div v-if="schedule.examiners.length">
                        <dt class="font-medium">Penguji</dt>
                        <dd>{{ schedule.examiners.join(', ') }}</dd>
                    </div>
                </dl>
            </li>
        </ul>
    </section>
</template>
