<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
const page = usePage<{ flash: { success?: string } }>();
const form = useForm({ email: '' });
const submit = () => {
    if (!form.processing) form.post('/forgot-password');
};
</script>
<template>
    <main
        class="min-h-screen bg-slate-50 px-4 py-16 text-slate-900 dark:bg-slate-950 dark:text-white"
    >
        <Head title="Akses pertama / Lupa password" />
        <section
            class="mx-auto max-w-md space-y-5 rounded-2xl border border-slate-300 bg-white p-6 dark:border-slate-700 dark:bg-slate-900"
        >
            <h1 class="text-xl font-bold">Akses pertama / Lupa password</h1>
            <p>
                Gunakan email terdaftar. Tautan pribadi berlaku 60 menit. Jika
                tidak memiliki akses email, hubungi pengelola untuk verifikasi
                identitas.
            </p>
            <p v-if="page.props.flash?.success" role="status">
                {{ page.props.flash.success }}
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
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full rounded bg-blue-700 p-3 text-white disabled:opacity-50"
                >
                    {{
                        form.processing
                            ? 'Memproses...'
                            : 'Kirim tautan password'
                    }}
                </button>
            </form>
            <Link href="/login" class="block underline">Kembali ke login</Link>
        </section>
    </main>
</template>
