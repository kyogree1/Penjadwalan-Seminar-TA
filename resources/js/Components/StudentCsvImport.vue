<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { onBeforeUnmount, ref } from 'vue';
import Button from '@/Components/Button.vue';

type Preview = {
    valid: boolean;
    errors: string[];
    rows: {
        row: number;
        data: Record<string, string> | null;
        errors: Record<string, string[]>;
    }[];
};

const file = ref<File | null>(null);
const preview = ref<Preview | null>(null);
const busy = ref(false);
const error = ref('');
const notice = ref('');
let controller: AbortController | null = null;

onBeforeUnmount(() => controller?.abort());

const selectFile = async (event: Event) => {
    controller?.abort();
    file.value = (event.target as HTMLInputElement).files?.[0] ?? null;
    preview.value = null;
    error.value = '';
    notice.value = '';
    if (!file.value) return;
    if (
        !file.value.name.toLowerCase().endsWith('.csv') ||
        file.value.size > 1024 * 1024
    ) {
        error.value = 'Pilih berkas CSV UTF-8 maksimal 1 MiB.';
        return;
    }
    const selected = file.value;
    const request = new AbortController();
    controller = request;
    busy.value = true;
    const data = new FormData();
    data.append('file', selected);
    try {
        const token = document.cookie
            .split('; ')
            .find((value) => value.startsWith('XSRF-TOKEN='));
        const response = await fetch('/kaprodi/mahasiswa/import-preview', {
            method: 'POST',
            credentials: 'same-origin',
            signal: request.signal,
            headers: {
                Accept: 'application/json',
                'X-XSRF-TOKEN': token
                    ? decodeURIComponent(token.slice(11))
                    : '',
            },
            body: data,
        });
        const result = await response.json();
        if (!response.ok) throw new Error(result.message ?? 'Preview gagal.');
        if (file.value === selected) preview.value = result;
    } catch (caught) {
        if (!request.signal.aborted)
            error.value =
                caught instanceof Error ? caught.message : 'Preview gagal.';
    } finally {
        if (controller === request) busy.value = false;
    }
};

const save = () => {
    if (!file.value || !preview.value?.valid || busy.value) return;
    busy.value = true;
    error.value = '';
    notice.value = '';
    router.post(
        '/kaprodi/mahasiswa/import',
        { file: file.value },
        {
            preserveScroll: true,
            onSuccess: (page) => {
                const flash = page.props.flash as
                    | { success?: string; error?: string }
                    | undefined;
                if (flash?.success && !flash.error) {
                    notice.value = flash.success;
                    preview.value = null;
                    file.value = null;
                } else
                    error.value =
                        flash?.error ?? 'Penyimpanan belum dikonfirmasi.';
            },
            onError: (errors) => {
                error.value = Object.values(errors).join(' ');
            },
            onNetworkError: () => {
                error.value = 'Koneksi gagal. Import belum dikonfirmasi.';
                return false;
            },
            onFinish: () => {
                busy.value = false;
            },
        },
    );
};
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Format CSV UTF-8 maksimal 1 MiB / 500 baris. Kolom: nama,
                    nim, angkatan, jurusan, prodi, email, no_telpon, status_ta.
                </p>
                <p class="mt-1 text-xs text-slate-400">
                    Duplikat NIM dan email ditolak secara otomatis. Password
                    default acak di-generate secara aman.
                </p>
            </div>
            <a
                href="/kaprodi/mahasiswa/template"
                download
                class="shrink-0 rounded-xl border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-600 hover:bg-blue-100 dark:border-blue-900/50 dark:bg-blue-950/40 dark:text-blue-400"
            >
                📥 Unduh Template CSV
            </a>
        </div>

        <div class="space-y-2">
            <label
                for="student-csv-file"
                class="block text-xs font-semibold text-slate-700 dark:text-slate-300"
            >
                Pilih Berkas CSV
            </label>
            <input
                id="student-csv-file"
                type="file"
                accept=".csv,text/csv"
                :disabled="busy"
                class="w-full cursor-pointer rounded-xl border border-slate-200 bg-slate-50 p-2 text-xs text-slate-700 file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-blue-600 file:px-3 file:py-1 file:text-xs file:font-semibold file:text-white hover:file:bg-blue-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                @change="selectFile"
            />
        </div>

        <p
            v-if="busy"
            role="status"
            class="text-xs text-blue-600 dark:text-blue-400"
        >
            Memproses preview data CSV…
        </p>
        <p
            v-if="error"
            role="alert"
            class="rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs font-medium text-rose-700 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-300"
        >
            {{ error }}
        </p>
        <p
            v-if="notice"
            role="status"
            class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-xs font-medium text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-300"
        >
            {{ notice }}
        </p>

        <template v-if="preview">
            <div v-if="preview.errors.length" class="space-y-1">
                <p
                    v-for="message in preview.errors"
                    :key="message"
                    role="alert"
                    class="text-xs font-semibold text-rose-600"
                >
                    ⚠️ {{ message }}
                </p>
            </div>

            <div
                class="max-h-72 overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800"
            >
                <table class="w-full text-left text-xs">
                    <thead
                        class="bg-slate-50 font-bold text-slate-600 dark:bg-slate-900 dark:text-slate-400"
                    >
                        <tr>
                            <th class="p-2.5 text-center">Baris</th>
                            <th class="p-2.5">
                                Data (Nama / NIM / Angkatan / Prodi / Email)
                            </th>
                            <th class="p-2.5">Status Validasi</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-slate-100 dark:divide-slate-800"
                    >
                        <tr
                            v-for="row in preview.rows"
                            :key="row.row"
                            class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40"
                        >
                            <td class="p-2.5 text-center font-mono">
                                {{ row.row }}
                            </td>
                            <td class="p-2.5">
                                {{
                                    row.data
                                        ? Object.values(row.data).join(' • ')
                                        : 'Data tidak valid'
                                }}
                            </td>
                            <td class="p-2.5">
                                <span
                                    v-if="Object.keys(row.errors).length === 0"
                                    class="inline-flex items-center text-xs font-bold text-emerald-600 dark:text-emerald-400"
                                >
                                    ✓ Valid
                                </span>
                                <div v-else class="space-y-0.5">
                                    <p
                                        v-for="(messages, field) in row.errors"
                                        :key="field"
                                        class="text-[11px] font-semibold text-rose-600 dark:text-rose-400"
                                    >
                                        {{ field }}: {{ messages.join(' ') }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <Button
                    variant="primary"
                    size="sm"
                    :disabled="busy || !preview.valid"
                    @click="save"
                >
                    Simpan Import CSV ({{ preview.rows.length }} baris)
                </Button>
            </div>
        </template>
    </div>
</template>
