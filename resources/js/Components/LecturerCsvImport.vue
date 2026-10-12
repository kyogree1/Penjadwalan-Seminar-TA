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
        error.value = 'Pilih CSV UTF-8 maksimal 1 MiB.';
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
        const response = await fetch('/kaprodi/dosen/import/preview', {
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
        '/kaprodi/dosen/import',
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
    <section class="space-y-3 rounded border p-4">
        <h2 class="font-bold">Import CSV dosen</h2>
        <p>
            CSV UTF-8, maksimal 1 MiB / 500 baris. Kolom:
            name,email,nip,prodi,jabatan. NIP dipertahankan sebagai teks.
        </p>
        <p>
            Duplikat NIP/username dan email (semua peran atau dalam file)
            ditolak. Seluruh file harus valid; tidak ada pembaruan atau import
            parsial. Password acak tidak ditampilkan; provisioning akun
            memerlukan proses terpisah. XLSX belum tersedia.
        </p>
        <a href="/kaprodi/dosen/template" class="underline"
            >Unduh template CSV</a
        >
        <label for="lecturer-csv">Pilih file CSV</label>
        <input
            id="lecturer-csv"
            type="file"
            accept=".csv,text/csv"
            :disabled="busy"
            @change="selectFile"
        />
        <p v-if="busy" role="status">Memproses…</p>
        <p v-if="error" role="alert">{{ error }}</p>
        <p v-if="notice" role="status">{{ notice }}</p>
        <template v-if="preview">
            <p v-for="message in preview.errors" :key="message" role="alert">
                {{ message }}
            </p>
            <div class="max-h-80 overflow-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr>
                            <th>Baris</th>
                            <th>Nama / email / NIP / prodi / jabatan</th>
                            <th>Kesalahan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in preview.rows" :key="row.row">
                            <td>{{ row.row }}</td>
                            <td>
                                {{
                                    row.data
                                        ? Object.values(row.data).join(' / ')
                                        : 'Kolom tidak valid'
                                }}
                            </td>
                            <td>
                                <p
                                    v-for="(messages, field) in row.errors"
                                    :key="field"
                                >
                                    {{ field }}: {{ messages.join(' ') }}
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <Button :disabled="busy || !preview.valid" @click="save"
                >Simpan import CSV</Button
            >
        </template>
    </section>
</template>
