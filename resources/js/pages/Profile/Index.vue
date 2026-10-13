<script setup lang="ts">
import { computed, watch } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { Info, ShieldCheck } from 'lucide-vue-next';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';
interface ProfileUser {
    id: number;
    name: string;
    email: string;
    role: string;
    nim_nip?: string | null;
    prodi?: string | null;
}
const props = defineProps<{ user: ProfileUser }>();
const profile = computed(() => props.user);
const page = usePage();
const feedback = computed(
    () => (page.props.flash as { success?: string; error?: string }) ?? {},
);
const form = useForm({ name: props.user.name, email: props.user.email });
watch(
    () => props.user,
    (user) => {
        form.name = user.name;
        form.email = user.email;
    },
);
const userRoleBadge = computed(() =>
    props.user.role === 'koordinator' ? 'kaprodi' : props.user.role,
);
const userInitials = computed(() =>
    props.user.name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((word) => word[0]?.toUpperCase())
        .join(''),
);
const submit = () => {
    if (!form.processing) form.patch('/profile', { preserveScroll: true });
};
</script>

<template>
    <Head title="Profil Pengguna - SIPTA IF" />

    <div class="mx-auto max-w-5xl space-y-6">
        <!-- Header Box (Reusable Component) -->
        <PageHeaderBox title="Profil Pengguna" subtitle="Data akun tersimpan" />

        <!-- Warning PENTING SOP SIPTA IF -->
        <div
            class="rounded-2xl border border-yellow-200 bg-[#FCFDE1] p-5 shadow-xs dark:border-yellow-900/50 dark:bg-yellow-950/30"
        >
            <div class="flex items-start gap-3">
                <Info class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" />
                <div
                    class="space-y-1 text-xs leading-relaxed text-slate-800 dark:text-yellow-200"
                >
                    <p class="font-bold">
                        Ketentuan e-TTD & Data Akademik (SIPTA IF):
                    </p>
                    <p>
                        Nama dan email dapat diperbarui melalui server.
                        Angkatan, kontak telepon, dan e-TTD belum memiliki
                        kontrak penyimpanan.
                    </p>
                </div>
            </div>
        </div>

        <p v-if="feedback.success" role="status" class="text-emerald-700">
            {{ feedback.success }}
        </p>
        <p v-if="feedback.error" role="alert" class="text-rose-700">
            {{ feedback.error }}
        </p>
        <p
            v-for="error in form.errors"
            :key="error"
            role="alert"
            class="text-rose-700"
        >
            {{ error }}
        </p>
        <!-- Profile Data Grid -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <!-- Left: Avatar Card & Signature Status -->
            <div
                class="flex flex-col justify-between rounded-2xl border border-slate-200/80 bg-white p-6 text-center shadow-xs dark:border-slate-800 dark:bg-[#0E1626]"
            >
                <div>
                    <div
                        class="mx-auto flex h-24 w-24 items-center justify-center rounded-full bg-blue-100 text-2xl font-bold text-blue-700 shadow-md dark:bg-blue-950 dark:text-blue-300"
                    >
                        {{ userInitials }}
                    </div>
                    <h3
                        class="mt-4 text-base font-bold text-slate-900 dark:text-white"
                    >
                        {{ form.name }}
                    </h3>
                    <p class="text-xs font-semibold text-slate-400">
                        {{ profile.nim_nip ?? 'Belum tersedia' }}
                    </p>
                    <span
                        class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300"
                    >
                        <ShieldCheck class="h-3.5 w-3.5" /> {{ userRoleBadge }}
                    </span>
                </div>

                <p class="mt-6 text-sm text-slate-500">
                    e-TTD belum tersedia: belum ada kontrak penyimpanan tanda
                    tangan.
                </p>
            </div>

            <!-- Right: Biodata Detail Form (Span 2) -->
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs md:col-span-2 dark:border-slate-800 dark:bg-[#0E1626]"
            >
                <h3
                    class="border-b border-slate-100 pb-4 text-base font-bold text-slate-900 dark:border-slate-800 dark:text-white"
                >
                    Biodata Pengguna
                </h3>

                <div class="mt-5 grid grid-cols-1 gap-4 text-xs sm:grid-cols-2">
                    <div class="space-y-1">
                        <label
                            class="font-bold text-slate-500 dark:text-slate-400"
                            >Nama Lengkap</label
                        >
                        <input
                            v-model="form.name"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 font-semibold text-slate-800 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                        />
                    </div>

                    <div class="space-y-1">
                        <label
                            class="font-bold text-slate-500 dark:text-slate-400"
                            >Nomor Induk (NIM / NIP)</label
                        >
                        <input
                            :value="profile.nim_nip ?? 'Belum tersedia'"
                            readonly
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 font-semibold text-slate-800 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                        />
                    </div>

                    <div class="space-y-1">
                        <label
                            class="font-bold text-slate-500 dark:text-slate-400"
                            >Program Studi</label
                        >
                        <input
                            :value="profile.prodi ?? 'Belum tersedia'"
                            readonly
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 font-semibold text-slate-800 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                        />
                    </div>

                    <div class="space-y-1">
                        <label
                            class="font-bold text-slate-500 dark:text-slate-400"
                            >Email Kampus ITK</label
                        >
                        <input
                            v-model="form.email"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 font-semibold text-slate-800 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                        />
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button
                        type="button"
                        :disabled="form.processing"
                        @click="submit"
                        class="rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-blue-700"
                    >
                        Simpan Nama &amp; Email
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
