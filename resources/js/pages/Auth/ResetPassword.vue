<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
const props = defineProps<{ token: string; email: string }>();
const form = useForm({
    email: props.email,
    token: props.token,
    password: '',
    password_confirmation: '',
});
const submit = () => {
    if (!form.processing)
        form.post('/reset-password', {
            onFinish: () => form.reset('password', 'password_confirmation'),
        });
};
</script>
<template>
    <main
        class="min-h-screen bg-slate-50 px-4 py-16 text-slate-900 dark:bg-slate-950 dark:text-white"
    >
        <Head title="Atur password" />
        <section
            class="mx-auto max-w-md space-y-5 rounded-2xl border border-slate-300 bg-white p-6 dark:border-slate-700 dark:bg-slate-900"
        >
            <h1 class="text-xl font-bold">Atur password</h1>
            <p>
                Minimal 12 karakter dengan huruf besar, huruf kecil, angka dan
                simbol. Semua sesi lama akan diakhiri.
            </p>
            <form class="space-y-4" @submit.prevent="submit">
                <label for="reset-email" class="block">Email terdaftar</label>
                <input
                    id="reset-email"
                    v-model="form.email"
                    type="email"
                    autocomplete="email"
                    required
                    maxlength="255"
                    class="w-full rounded border border-slate-400 p-3"
                />
                <p v-if="form.errors.email" role="alert">
                    {{ form.errors.email }}
                </p>
                <p v-if="form.errors.token" role="alert">
                    {{ form.errors.token }}
                </p>
                <label for="new-password" class="block">Password baru</label>
                <input
                    id="new-password"
                    v-model="form.password"
                    type="password"
                    autocomplete="new-password"
                    required
                    minlength="12"
                    maxlength="255"
                    class="w-full rounded border border-slate-400 p-3"
                />
                <p v-if="form.errors.password" role="alert">
                    {{ form.errors.password }}
                </p>
                <label for="password-confirmation" class="block"
                    >Konfirmasi password</label
                >
                <input
                    id="password-confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    required
                    minlength="12"
                    maxlength="255"
                    class="w-full rounded border border-slate-400 p-3"
                />
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full rounded bg-blue-700 p-3 text-white disabled:opacity-50"
                >
                    {{ form.processing ? 'Memproses...' : 'Simpan password' }}
                </button>
            </form>
            <Link href="/forgot-password" class="block underline"
                >Minta tautan baru</Link
            >
            <Link href="/login" class="block underline">Kembali ke login</Link>
        </section>
    </main>
</template>
