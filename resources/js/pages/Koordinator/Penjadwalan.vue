<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';
import type {
    OptimizerResult,
    PersistedSchedule,
    SchedulePeriod,
    SchedulePublication,
    ScheduleSlot,
} from '@/types/scheduling';

const props = defineProps<{
    periods: SchedulePeriod[];
    selectedPeriodId: number | null;
    penjadwalanList: PersistedSchedule[];
    dosenOptions: { id: number; name: string }[];
    capabilities: {
        lecturerKbk: boolean;
        availability: boolean;
        lecturerQuota: boolean;
    };
}>();
const selected = ref<number | null>(props.selectedPeriodId);
watch(
    () => props.selectedPeriodId,
    (value) => {
        selected.value = value;
        preview.value = null;
    },
);
const period = computed(() =>
    props.periods.find((item) => item.id === selected.value),
);
const schedules = computed(() =>
    props.penjadwalanList.filter(
        (row) => selected.value === null || row.period_id === selected.value,
    ),
);
const preview = ref<OptimizerResult | null>(null);
const error = ref('');
const notice = ref('');
const busy = ref(false);
let controller: AbortController | null = null;
onBeforeUnmount(() => controller?.abort());
const params = ref({
    popSize: 50,
    maxGenerations: 100,
    crossoverRate: 0.8,
    mutationRate: 0.03,
});
const changePeriod = () => {
    controller?.abort();
    preview.value = null;
    router.get(
        '/kaprodi/penjadwalan',
        selected.value === null ? {} : { period_id: selected.value },
        { preserveState: true },
    );
};
const generate = async () => {
    if (!period.value || busy.value) return;
    const periodId = period.value.id;
    busy.value = true;
    error.value = '';
    notice.value = '';
    preview.value = null;
    controller?.abort();
    const request = new AbortController();
    controller = request;
    try {
        const cookie = document.cookie
            .split('; ')
            .find((value) => value.startsWith('XSRF-TOKEN='));
        const response = await fetch('/kaprodi/penjadwalan/generate-ga', {
            method: 'POST',
            credentials: 'same-origin',
            signal: request.signal,
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-XSRF-TOKEN': cookie
                    ? decodeURIComponent(cookie.slice(11))
                    : '',
            },
            body: JSON.stringify({ period_id: periodId, ...params.value }),
        });
        const result = await response.json();
        if (!response.ok) throw new Error(result.message ?? 'Generate gagal.');
        if (!request.signal.aborted && selected.value === periodId)
            preview.value = result as OptimizerResult;
    } catch (caught) {
        if (!request.signal.aborted)
            error.value =
                caught instanceof Error
                    ? caught.message
                    : 'Generate gagal. Coba lagi.';
    } finally {
        if (controller === request) busy.value = false;
    }
};
const callbacks = (done: () => void) => ({
    preserveScroll: true,
    onSuccess: (page: { props: Record<string, unknown> }) => {
        const flash = page.props.flash as
            | { success?: string | null; error?: string | null }
            | undefined;
        if (flash?.error) {
            error.value = flash.error;
            return;
        }
        if (!flash?.success) {
            error.value = 'Penyimpanan belum dikonfirmasi.';
            return;
        }
        error.value = '';
        notice.value = flash.success;
        done();
    },
    onError: (errors: Record<string, string>) => {
        error.value = Object.values(errors).join(' ');
    },
    onNetworkError: () => {
        error.value = 'Koneksi gagal. Coba lagi.';
    },
    onFinish: () => {
        busy.value = false;
    },
});
const publication = (row: SchedulePublication) => ({
    pendaftaran_id: row.pendaftaran_id,
    mahasiswa_id: row.mahasiswa_id,
    tipe: row.tipe,
    penguji_1_id: row.penguji_1_id,
    penguji_2_id: row.penguji_2_id,
    tanggal: row.tanggal.slice(0, 10),
    jam_mulai: row.jam_mulai,
    jam_selesai: row.jam_selesai,
});
const publish = () => {
    if (!preview.value?.schedule.length || busy.value) return;
    error.value = '';
    notice.value = '';
    busy.value = true;
    router.post(
        '/kaprodi/penjadwalan/simpan',
        {
            period_id: preview.value.period_id,
            schedules: preview.value.schedule.map(publication),
        },
        callbacks(() => {
            preview.value = null;
        }),
    );
};
const editing = ref<PersistedSchedule | null>(null);
const override = ref({ penguji_1_id: 0, penguji_2_id: 0, slot: 0 });
const edit = (row: PersistedSchedule) => {
    editing.value = row;
    error.value = '';
    override.value = {
        penguji_1_id: row.penguji_1_id,
        penguji_2_id: row.penguji_2_id,
        slot: Math.max(
            0,
            period.value?.slots.findIndex(
                (slot) =>
                    slot.tanggal === row.tanggal.slice(0, 10) &&
                    slot.jam_mulai === row.jam_mulai,
            ) ?? 0,
        ),
    };
};
const saveOverride = () => {
    const slot = period.value?.slots[override.value.slot];
    if (!editing.value || !slot || busy.value) return;
    busy.value = true;
    error.value = '';
    notice.value = '';
    router.patch(
        `/kaprodi/penjadwalan/${editing.value.id}`,
        {
            penguji_1_id: override.value.penguji_1_id,
            penguji_2_id: override.value.penguji_2_id,
            ...slot,
        },
        callbacks(() => {
            editing.value = null;
        }),
    );
};
const draft = ref<Omit<SchedulePeriod, 'id'>>({
    name: '',
    tipe: 'sempro',
    gelombang: 1,
    capacity: 20,
    start_date: '',
    end_date: '',
    is_open: false,
    slots: [],
});
const slotDraft = ref<ScheduleSlot>({
    tanggal: '',
    jam_mulai: '09:00:00',
    jam_selesai: '10:30:00',
});
const editingPeriod = ref<number | null>(null);
const periodForm = ref(false);
const openPeriod = (item?: SchedulePeriod) => {
    editingPeriod.value = item?.id ?? null;
    draft.value = item
        ? { ...item, slots: item.slots.map((slot) => ({ ...slot })) }
        : {
              name: '',
              tipe: 'sempro',
              gelombang: 1,
              capacity: 20,
              start_date: '',
              end_date: '',
              is_open: false,
              slots: [],
          };
    periodForm.value = true;
    error.value = '';
};
const savePeriod = () => {
    if (busy.value) return;
    busy.value = true;
    error.value = '';
    notice.value = '';
    const options = callbacks(() => {
        periodForm.value = false;
    });
    if (editingPeriod.value !== null)
        router.patch(
            `/kaprodi/penjadwalan/periods/${editingPeriod.value}`,
            { ...draft.value },
            options,
        );
    else
        router.post(
            '/kaprodi/penjadwalan/periods',
            { ...draft.value },
            options,
        );
};
const intervalConflicts = computed(() => {
    let count = 0;
    const rows = schedules.value;
    for (let i = 0; i < rows.length; i++) {
        const a = rows[i];
        for (let j = i + 1; j < rows.length; j++) {
            const b = rows[j];
            if (
                a.tanggal.slice(0, 10) === b.tanggal.slice(0, 10) &&
                a.jam_mulai < b.jam_selesai &&
                b.jam_mulai < a.jam_selesai &&
                [a.mahasiswa_id, a.penguji_1_id, a.penguji_2_id].some((id) =>
                    [b.mahasiswa_id, b.penguji_1_id, b.penguji_2_id].includes(
                        id,
                    ),
                )
            )
                count++;
        }
    }
    return count;
});
</script>

<template>
    <Head title="Penjadwalan Ujian" />
    <div class="space-y-6">
        <PageHeaderBox
            title="Periode dan Penjadwalan Ujian"
            subtitle="Preview GA diperiksa ulang terhadap booking saat publikasi."
        >
            <template #action
                ><Button @click="openPeriod()">Tambah periode</Button></template
            >
        </PageHeaderBox>
        <p v-if="error" role="alert" class="text-rose-600">{{ error }}</p>
        <p v-if="notice" role="status" class="text-emerald-700">{{ notice }}</p>
        <Card class="space-y-4 p-5">
            <label for="selected-period">Periode</label>
            <select
                id="selected-period"
                v-model="selected"
                class="rounded border p-2"
                @change="changePeriod"
            >
                <option :value="null">Semua jadwal / historis</option>
                <option
                    v-for="item in props.periods"
                    :key="item.id"
                    :value="item.id"
                >
                    {{ item.name }} — {{ item.tipe }} ({{
                        item.is_open ? 'Terbuka' : 'Ditutup'
                    }})
                </option>
            </select>
            <Button v-if="period" @click="openPeriod(period)"
                >Edit periode</Button
            >
            <p v-if="!props.periods.length">Belum ada periode tersimpan.</p>
            <p v-if="period">
                {{ period.start_date.slice(0, 10) }} –
                {{ period.end_date.slice(0, 10) }} ·
                {{ period.slots.length }} slot · kapasitas {{ period.capacity }}
            </p>
            <p>
                {{ schedules.length }} jadwal tersimpan ·
                {{ intervalConflicts }} pasangan konflik mahasiswa/penguji di
                daftar ini. Server memeriksa supervisor dan seluruh periode.
            </p>
            <p
                v-if="
                    !props.capabilities.lecturerKbk ||
                    !props.capabilities.availability ||
                    !props.capabilities.lecturerQuota
                "
                class="text-sm text-slate-500"
            >
                KBK dosen, kalender ketersediaan dan kuota dosen belum tersedia.
                Tidak ada klaim match KBK atau waktu luang nyata. Kapasitas
                periode dan booking tersimpan diperiksa.
            </p>
            <div class="flex flex-wrap gap-3">
                <label
                    >Populasi
                    <input
                        v-model.number="params.popSize"
                        type="number"
                        min="10"
                        max="200"
                        class="w-20 rounded border p-2"
                /></label>
                <label
                    >Generasi
                    <input
                        v-model.number="params.maxGenerations"
                        type="number"
                        min="10"
                        max="500"
                        class="w-20 rounded border p-2"
                /></label>
                <label
                    >Crossover
                    <input
                        v-model.number="params.crossoverRate"
                        type="number"
                        min="0"
                        max="1"
                        step="0.01"
                        class="w-20 rounded border p-2"
                /></label>
                <label
                    >Mutasi
                    <input
                        v-model.number="params.mutationRate"
                        type="number"
                        min="0"
                        max="1"
                        step="0.01"
                        class="w-20 rounded border p-2"
                /></label>
                <Button :disabled="busy || !period?.is_open" @click="generate"
                    >Generate</Button
                >
            </div>
            <p v-if="busy" role="status">Permintaan sedang diproses…</p>
        </Card>
        <Card v-if="preview" class="space-y-3 p-5">
            <h2 class="font-bold">Preview: {{ preview.outcome }}</h2>
            <p>
                {{ preview.schedule.length }} dijadwalkan ·
                {{ preview.unscheduled.length }} belum dijadwalkan · fitness
                {{ preview.fitness }} · KBK {{ preview.kbk_match }} ·
                keseimbangan {{ preview.workload_balance }} ·
                {{ preview.generations }} generasi
            </p>
            <p v-for="log in preview.logs" :key="log">{{ log }}</p>
            <p
                v-for="limit in preview.limitations"
                :key="limit"
                class="text-sm text-slate-500"
            >
                {{ limit }}
            </p>
            <ul>
                <li
                    v-for="row in preview.schedule"
                    :key="`${row.tipe}:${row.pendaftaran_id}`"
                >
                    {{ row.nama }} · {{ row.tipe }} #{{ row.pendaftaran_id }} ·
                    {{ row.tanggal }} {{ row.jam_mulai }} –
                    {{ row.jam_selesai }} · penguji #{{ row.penguji_1_id }}, #{{
                        row.penguji_2_id
                    }}
                </li>
            </ul>
            <Button
                :disabled="busy || !preview.schedule.length"
                @click="publish"
                >Publikasikan preview</Button
            >
            <Button :disabled="busy" @click="preview = null"
                >Buang preview</Button
            >
        </Card>
        <Card class="overflow-x-auto p-5">
            <h2 class="mb-3 font-bold">Jadwal tersimpan</h2>
            <p v-if="!schedules.length">Belum ada jadwal tersimpan.</p>
            <table v-else class="w-full text-left text-sm">
                <thead>
                    <tr>
                        <th>Mahasiswa</th>
                        <th>Jenis / pendaftaran</th>
                        <th>Penguji</th>
                        <th>Waktu</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in schedules" :key="row.id" class="border-t">
                        <td class="p-2">
                            {{
                                row.mahasiswa?.name ??
                                'Mahasiswa tidak tersedia'
                            }}
                        </td>
                        <td>{{ row.tipe }} #{{ row.pendaftaran_id }}</td>
                        <td>
                            {{ row.penguji1?.name ?? 'Belum ditetapkan' }} /
                            {{ row.penguji2?.name ?? 'Belum ditetapkan' }}
                        </td>
                        <td>
                            {{ row.tanggal.slice(0, 10) }} {{ row.jam_mulai }} –
                            {{ row.jam_selesai }}
                        </td>
                        <td>{{ row.status }}</td>
                        <td>
                            <Button
                                :disabled="
                                    busy ||
                                    !period ||
                                    !row.period_id ||
                                    row.status === 'selesai'
                                "
                                @click="edit(row)"
                                >Edit jadwal</Button
                            >
                        </td>
                    </tr>
                </tbody>
            </table>
        </Card>
        <Card v-if="editing" class="space-y-3 p-5">
            <h2 class="font-bold">Penyesuaian {{ editing.mahasiswa?.name }}</h2>
            <p v-if="error" role="alert" class="text-rose-600">{{ error }}</p>
            <label
                >Penguji 1
                <select
                    v-model="override.penguji_1_id"
                    class="rounded border p-2"
                >
                    <option
                        v-for="lecturer in props.dosenOptions"
                        :key="lecturer.id"
                        :value="lecturer.id"
                    >
                        {{ lecturer.name }}
                    </option>
                </select></label
            >
            <label
                >Penguji 2
                <select
                    v-model="override.penguji_2_id"
                    class="rounded border p-2"
                >
                    <option
                        v-for="lecturer in props.dosenOptions"
                        :key="lecturer.id"
                        :value="lecturer.id"
                    >
                        {{ lecturer.name }}
                    </option>
                </select></label
            >
            <label
                >Slot
                <select v-model="override.slot" class="rounded border p-2">
                    <option
                        v-for="(slot, index) in period?.slots"
                        :key="index"
                        :value="index"
                    >
                        {{ slot.tanggal }} {{ slot.jam_mulai }} –
                        {{ slot.jam_selesai }}
                    </option>
                </select></label
            >
            <Button :disabled="busy" @click="saveOverride"
                >Simpan penyesuaian</Button
            ><Button :disabled="busy" @click="editing = null">Batal</Button>
        </Card>
        <Card v-if="periodForm" class="space-y-3 p-5">
            <h2 class="font-bold">
                {{ editingPeriod ? 'Edit' : 'Tambah' }} periode
            </h2>
            <p v-if="error" role="alert" class="text-rose-600">{{ error }}</p>
            <form class="flex flex-wrap gap-3" @submit.prevent="savePeriod">
                <label
                    >Nama
                    <input
                        v-model="draft.name"
                        required
                        class="rounded border p-2"
                /></label>
                <label
                    >Jenis
                    <select v-model="draft.tipe" class="rounded border p-2">
                        <option value="sempro">Sempro</option>
                        <option value="sidang">Sidang</option>
                    </select></label
                >
                <label
                    >Gelombang
                    <input
                        v-model.number="draft.gelombang"
                        type="number"
                        min="1"
                        required
                        class="w-20 rounded border p-2"
                /></label>
                <label
                    >Kapasitas
                    <input
                        v-model.number="draft.capacity"
                        type="number"
                        min="1"
                        required
                        class="w-20 rounded border p-2"
                /></label>
                <label
                    >Mulai
                    <input
                        v-model="draft.start_date"
                        type="date"
                        required
                        class="rounded border p-2"
                /></label>
                <label
                    >Selesai
                    <input
                        v-model="draft.end_date"
                        type="date"
                        required
                        class="rounded border p-2"
                /></label>
                <label
                    ><input v-model="draft.is_open" type="checkbox" />
                    Terbuka</label
                >
                <div class="w-full space-y-2">
                    <label
                        >Tanggal slot
                        <input
                            v-model="slotDraft.tanggal"
                            type="date"
                            class="rounded border p-2"
                    /></label>
                    <label
                        >Jam mulai
                        <input
                            v-model="slotDraft.jam_mulai"
                            type="time"
                            step="1"
                            class="rounded border p-2"
                    /></label>
                    <label
                        >Jam selesai
                        <input
                            v-model="slotDraft.jam_selesai"
                            type="time"
                            step="1"
                            class="rounded border p-2"
                    /></label>
                    <Button
                        type="button"
                        @click="draft.slots.push({ ...slotDraft })"
                        >Tambah slot</Button
                    >
                    <ul>
                        <li v-for="(slot, index) in draft.slots" :key="index">
                            {{ slot.tanggal }} {{ slot.jam_mulai }} –
                            {{ slot.jam_selesai }}
                            <button
                                type="button"
                                @click="draft.slots.splice(index, 1)"
                            >
                                Hapus slot
                            </button>
                        </li>
                    </ul>
                </div>
                <Button type="submit" :disabled="busy">Simpan periode</Button
                ><Button
                    type="button"
                    :disabled="busy"
                    @click="periodForm = false"
                    >Batal</Button
                >
            </form>
        </Card>
        <p class="text-sm text-slate-500">
            <a
                :href="
                    selected === null
                        ? '/kaprodi/penjadwalan/export'
                        : `/kaprodi/penjadwalan/export?period_id=${selected}`
                "
                class="underline"
                >Unduh jadwal CSV</a
            >
            · XLSX dan PDF belum tersedia (tidak ada library pembuat file).
            Riwayat kelulusan dan penerbitan arsip belum tersedia.
        </p>
    </div>
</template>
