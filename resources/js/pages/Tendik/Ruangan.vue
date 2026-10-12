<script setup lang="ts">
import { computed, reactive, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import Modal from '@/Components/Modal.vue';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';
import type { TendikRoom, TendikRoomProps } from '@/types/tendik-facilities';
const props = defineProps<TendikRoomProps>();
const rooms = computed(() => props.dbRuanganList);
const mode = ref<'create' | 'edit' | 'delete' | null>(null);
const selectedId = ref<number | null>(null);
const selectedRoom = computed(() =>
    rooms.value.find((r) => r.id === selectedId.value),
);
const busy = ref(false);
const error = ref('');
const success = ref('');
const blank = () => ({
    nama_ruangan: '',
    gedung: null as string | null,
    lantai: null as string | null,
    kapasitas: 30,
    status: 'tersedia' as TendikRoom['status'],
    keterangan: null as string | null,
});
const form = reactive(blank());
const open = (action: 'create' | 'edit' | 'delete', room?: TendikRoom) => {
    if (busy.value) return;
    mode.value = action;
    selectedId.value = room?.id ?? null;
    error.value = '';
    success.value = '';
    const { id: _id, ...data } = room ?? { id: null, ...blank() };
    Object.assign(form, data);
};
const close = () => {
    if (!busy.value) mode.value = null;
};
const submit = () => {
    if (
        busy.value ||
        !mode.value ||
        (mode.value !== 'create' && !selectedRoom.value)
    )
        return;
    busy.value = true;
    error.value = '';
    success.value = '';
    const options = {
        preserveScroll: true,
        onSuccess: (page: { props: Record<string, unknown> }) => {
            const flash = page.props.flash as
                | { success?: string; error?: string }
                | undefined;
            if (flash?.error) error.value = flash.error;
            else if (flash?.success) {
                success.value = flash.success;
                mode.value = null;
            } else
                error.value =
                    'Konfirmasi server belum diterima. Muat ulang data sebelum mencoba kembali.';
        },
        onError: (errors: Record<string, string>) => {
            error.value = Object.values(errors).join(' ');
        },
        onNetworkError: () => {
            error.value = 'Koneksi gagal. Silakan coba kembali.';
        },
        onHttpException: () => {
            error.value =
                'Permintaan gagal diproses server. Silakan muat ulang data.';
        },
        onCancel: () => {
            error.value = 'Permintaan dibatalkan.';
        },
        onFinish: () => {
            busy.value = false;
        },
    };
    const payload = {
        ...form,
        gedung: form.gedung || null,
        lantai: form.lantai || null,
        keterangan: form.keterangan || null,
    };
    if (mode.value === 'create')
        router.post('/tendik/ruangan', payload, options);
    else if (mode.value === 'edit')
        router.put(`/tendik/ruangan/${selectedId.value}`, payload, options);
    else router.delete(`/tendik/ruangan/${selectedId.value}`, options);
};
</script>
<template>
    <Head title="Manajemen Ruangan - Portal Tendik" />
    <div class="space-y-6">
        <PageHeaderBox
            badge="Sarana & Prasarana"
            title="Kelola Ruangan"
            description="Status tersedia adalah pengaturan administratif, bukan kondisi pemakaian saat ini."
        />
        <div class="flex justify-end">
            <Button :disabled="busy" @click="open('create')"
                >Tambah Ruangan</Button
            >
        </div>
        <p v-if="success" role="status" class="text-emerald-700">
            {{ success }}
        </p>
        <p v-if="error && !mode" role="alert" class="text-red-600">
            {{ error }}
        </p>
        <p class="text-sm text-slate-500">
            Fasilitas terstruktur dan agenda pemakaian harian tidak tersedia
            pada halaman ini. Manajemen link hybrid ruangan belum tersedia; link
            online tersimpan per jadwal, bukan per ruangan.
        </p>
        <p
            v-if="!rooms.length"
            class="rounded-xl bg-slate-100 p-6 dark:bg-slate-900"
        >
            Belum ada ruangan terdaftar.
        </p>
        <div class="grid gap-6 md:grid-cols-2">
            <Card v-for="room in rooms" :key="room.id" class="space-y-3 p-6">
                <h3 class="font-bold">{{ room.nama_ruangan }}</h3>
                <p>
                    Gedung: {{ room.gedung || '—' }} • Lantai:
                    {{ room.lantai || '—' }}
                </p>
                <p>Kapasitas: {{ room.kapasitas }}</p>
                <p>
                    {{
                        room.status === 'tersedia'
                            ? 'Tersedia'
                            : 'Tidak tersedia'
                    }}
                </p>
                <p>Catatan: {{ room.keterangan || '—' }}</p>
                <div class="flex gap-2">
                    <Button
                        variant="outline"
                        :disabled="busy"
                        @click="open('edit', room)"
                        >Edit</Button
                    ><Button
                        variant="outline"
                        :disabled="busy"
                        @click="open('delete', room)"
                        >Hapus</Button
                    >
                </div>
            </Card>
        </div>
    </div>
    <Modal :show="mode !== null" @close="close"
        ><div class="space-y-4 p-6">
            <h3 class="font-bold">
                {{
                    mode === 'create'
                        ? 'Tambah Ruangan'
                        : mode === 'edit'
                          ? 'Edit Ruangan'
                          : 'Hapus Ruangan'
                }}
            </h3>
            <p v-if="mode !== 'create' && !selectedRoom" role="alert">
                Data ruangan tidak lagi tersedia.
            </p>
            <p v-if="mode === 'delete'">
                Hapus {{ selectedRoom?.nama_ruangan }}? Jadwal yang terkait
                tidak lagi memiliki referensi ruangan. Tindakan ini tidak
                membatalkan jadwal.
            </p>
            <form v-else class="grid gap-3" @submit.prevent="submit">
                <label
                    >Nama ruangan<input
                        v-model="form.nama_ruangan"
                        name="nama_ruangan"
                        required
                        maxlength="255"
                        :disabled="busy"
                        class="block w-full rounded border p-2"
                /></label>
                <label
                    >Gedung<input
                        v-model="form.gedung"
                        name="gedung"
                        maxlength="100"
                        :disabled="busy"
                        class="block w-full rounded border p-2"
                /></label>
                <label
                    >Lantai<input
                        v-model="form.lantai"
                        name="lantai"
                        maxlength="50"
                        :disabled="busy"
                        class="block w-full rounded border p-2"
                /></label>
                <label
                    >Kapasitas<input
                        v-model.number="form.kapasitas"
                        name="kapasitas"
                        type="number"
                        min="1"
                        required
                        :disabled="busy"
                        class="block w-full rounded border p-2"
                /></label>
                <label
                    >Status<select
                        v-model="form.status"
                        name="status"
                        :disabled="busy"
                        class="block w-full rounded border p-2"
                    >
                        <option value="tersedia">Tersedia</option>
                        <option value="tidak_tersedia">Tidak tersedia</option>
                    </select></label
                >
                <label
                    >Catatan<textarea
                        v-model="form.keterangan"
                        name="keterangan"
                        :disabled="busy"
                        class="block w-full rounded border p-2"
                    />
                </label>
            </form>
            <p v-if="error" role="alert" class="text-red-600">{{ error }}</p>
            <p v-if="busy" role="status">Memproses permintaan…</p>
            <div class="flex justify-end gap-2">
                <Button variant="secondary" :disabled="busy" @click="close"
                    >Batal</Button
                ><Button
                    :disabled="busy || (mode !== 'create' && !selectedRoom)"
                    @click="submit"
                    >{{ mode === 'delete' ? 'Ya, Hapus' : 'Simpan' }}</Button
                >
            </div>
        </div></Modal
    >
</template>
