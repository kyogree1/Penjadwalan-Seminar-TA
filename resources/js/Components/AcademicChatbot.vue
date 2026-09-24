<script setup lang="ts">
import { nextTick, ref } from 'vue';
import {
    Bot,
    ChevronDown,
    GraduationCap,
    HelpCircle,
    Maximize2,
    MessageSquare,
    Minimize2,
    Send,
    Sparkles,
    User,
    X,
} from 'lucide-vue-next';

interface ChatMessage {
    id: string;
    sender: 'bot' | 'user';
    text: string;
    time: string;
}

const isOpen = ref(false);
const isExpanded = ref(false);
const userInput = ref('');
const isTyping = ref(false);
const messagesContainer = ref<HTMLDivElement | null>(null);

const getTimeString = () => {
    const now = new Date();
    return (
        now.toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false,
        }) + ' WITA'
    );
};

const messages = ref<ChatMessage[]>([
    {
        id: 'msg-1',
        sender: 'bot',
        text: 'Halo Akmal! Saya Asisten AI Akademik Informatika ITK. Ada yang bisa saya bantu terkait SOP, bimbingan, berkas pendaftaran Sempro, atau jadwal Tugas Akhir Anda?',
        time: getTimeString(),
    },
]);

const quickPrompts = [
    'Apa syarat berkas daftar Sempro?',
    'Bagaimana ketentuan logbook bimbingan (Form TA-04)?',
    'Kapan batas penutupan gelombang 2?',
    'Aturan plagiasi Turnitin berapa persen?',
];

const scrollToBottom = async () => {
    await nextTick();
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop =
            messagesContainer.value.scrollHeight;
    }
};

const sendQuery = (text: string) => {
    if (!text.trim()) return;

    // Tambah pesan user
    messages.value.push({
        id: `user-${Date.now()}`,
        sender: 'user',
        text: text.trim(),
        time: getTimeString(),
    });

    userInput.value = '';
    scrollToBottom();

    // Simulasi berpikir AI LLM
    isTyping.value = true;
    setTimeout(() => {
        isTyping.value = false;
        let reply = '';

        const lower = text.toLowerCase();
        if (lower.includes('sempro') || lower.includes('syarat')) {
            reply =
                'Untuk pendaftaran **Seminar Proposal (Sempro)** di Informatika ITK, berkas yang wajib diunggah meliputi:\n1. Naskah Proposal TA (BAB 1–3) format PDF (maks 10 MB)\n2. Formulir Kesediaan Membimbing (Form TA-01A) & Persetujuan Sempro (Form TA-02)\n3. Lembar Kehadiran Seminar TA terdahulu (Form TA-03D, min. 5x hadir)\n4. Lembar Monitoring Bimbingan (Form TA-04, min. 8x bimbingan)\n5. Bukti Lolos Cek Plagiasi Turnitin (maksimal similarity index 20%).';
        } else if (
            lower.includes('bimbingan') ||
            lower.includes('ta-04') ||
            lower.includes('logbook')
        ) {
            reply =
                'Setiap mahasiswa wajib melakukan bimbingan minimal **8 kali bimbingan per semester** untuk masing-masing Dosen Pembimbing 1 dan 2. Catat setiap sesi di menu **Bimbingan** untuk kemudian diunduh sebagai **Form TA-04** ber-e-TTD.';
        } else if (
            lower.includes('gelombang') ||
            lower.includes('tutup') ||
            lower.includes('jadwal')
        ) {
            reply =
                'Periode pendaftaran aktif saat ini adalah **Gelombang 2 (Semester Gasal 2026/2027)**. Batas akhir submit berkas adalah **30 Oktober 2026 pukul 23.59 WITA**. Penjadwalan seminar akan otomatis dioptimasi oleh sistem genetika setelah pendaftaran ditutup.';
        } else if (lower.includes('turnitin') || lower.includes('plagiasi')) {
            reply =
                'Sesuai pedoman FSTI ITK, batas maksimal kesamaan teks pada Turnitin adalah **20%** dengan filter exclude quotes dan exclude bibliography aktif.';
        } else {
            reply = `Pertanyaan Anda mengenai "${text}" telah dicatat. Anda dapat berkonsultasi lebih lanjut dengan Koordinator Tugas Akhir Informatika ITK di Gedung Lab Terpadu atau memeriksa menu **Panduan Website (SIPTA IF)**.`;
        }

        messages.value.push({
            id: `bot-${Date.now()}`,
            sender: 'bot',
            text: reply,
            time: getTimeString(),
        });
        scrollToBottom();
    }, 900);
};

const handleSend = () => {
    sendQuery(userInput.value);
};
</script>

<template>
    <div
        class="fixed right-6 bottom-6 z-50 flex flex-col items-end select-none"
    >
        <!-- Chat Window Pop-up -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="transform scale-95 opacity-0 translate-y-4"
            enter-to-class="transform scale-100 opacity-100 translate-y-0"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="transform scale-100 opacity-100 translate-y-0"
            leave-to-class="transform scale-95 opacity-0 translate-y-4"
        >
            <div
                v-if="isOpen"
                class="flex flex-col overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-2xl transition-all duration-300 dark:border-slate-800 dark:bg-slate-900"
                :class="[
                    isExpanded
                        ? 'h-[85vh] w-[95vw] sm:w-[540px]'
                        : 'h-[540px] w-[90vw] sm:w-[400px]',
                ]"
            >
                <!-- Chat Window Header -->
                <div
                    class="flex items-center justify-between border-b border-blue-700/30 bg-linear-to-r from-blue-700 via-blue-600 to-indigo-700 px-4 py-3.5 text-white"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="relative flex h-9 w-9 items-center justify-center rounded-xl bg-white/20 shadow-xs backdrop-blur-md"
                        >
                            <Bot class="h-5 w-5 text-white" />
                            <span
                                class="absolute -top-0.5 -right-0.5 h-2.5 w-2.5 rounded-full bg-emerald-400 ring-2 ring-blue-700"
                            />
                        </div>
                        <div>
                            <div class="flex items-center gap-1.5">
                                <h3 class="text-sm font-bold tracking-tight">
                                    AI Layanan Akademik
                                </h3>
                                <span
                                    class="rounded-md bg-white/20 px-1.5 py-0.5 text-[10px] font-semibold text-blue-100"
                                >
                                    Informatika ITK
                                </span>
                            </div>
                            <p class="text-[11px] text-blue-100/90">
                                Asisten Cerdas SOP & Jadwal TA
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-1">
                        <button
                            type="button"
                            class="rounded-lg p-1.5 text-blue-100 transition-colors hover:bg-white/10 hover:text-white"
                            :title="isExpanded ? 'Perkecil' : 'Perbesar'"
                            @click="isExpanded = !isExpanded"
                        >
                            <Minimize2 v-if="isExpanded" class="h-4 w-4" />
                            <Maximize2 v-else class="h-4 w-4" />
                        </button>
                        <button
                            type="button"
                            class="rounded-lg p-1.5 text-blue-100 transition-colors hover:bg-white/10 hover:text-white"
                            title="Tutup Obrolan"
                            @click="isOpen = false"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                <!-- Chat Messages Scroll Area -->
                <div
                    ref="messagesContainer"
                    class="flex-1 space-y-3.5 overflow-y-auto bg-slate-50/70 p-4 dark:bg-slate-950/60"
                >
                    <!-- Quick Suggestions Prompt Chips -->
                    <div class="mb-2 space-y-1.5">
                        <p
                            class="text-[11px] font-semibold text-slate-400 uppercase dark:text-slate-500"
                        >
                            Pertanyaan Umum:
                        </p>
                        <div class="flex flex-wrap gap-1.5">
                            <button
                                v-for="(prompt, idx) in quickPrompts"
                                :key="idx"
                                type="button"
                                class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-left text-xs font-medium text-slate-700 shadow-2xs transition-all hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-500 dark:hover:bg-blue-950/40"
                                @click="sendQuery(prompt)"
                            >
                                {{ prompt }}
                            </button>
                        </div>
                    </div>

                    <!-- Messages List -->
                    <div
                        v-for="msg in messages"
                        :key="msg.id"
                        class="flex flex-col"
                        :class="[
                            msg.sender === 'user' ? 'items-end' : 'items-start',
                        ]"
                    >
                        <div
                            class="max-w-[85%] rounded-2xl px-3.5 py-2.5 text-xs leading-relaxed shadow-2xs"
                            :class="[
                                msg.sender === 'user'
                                    ? 'rounded-tr-xs bg-blue-600 text-white'
                                    : 'rounded-tl-xs border border-slate-200/80 bg-white text-slate-800 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200',
                            ]"
                        >
                            <p class="whitespace-pre-line">{{ msg.text }}</p>
                        </div>
                        <span
                            class="mt-1 px-1 text-[10px] text-slate-400 dark:text-slate-500"
                        >
                            {{ msg.time }}
                        </span>
                    </div>

                    <!-- Typing Indicator -->
                    <div
                        v-if="isTyping"
                        class="flex items-center gap-2 text-xs text-slate-500"
                    >
                        <div
                            class="flex items-center gap-1 rounded-full border border-slate-200/80 bg-white px-3 py-2 shadow-2xs dark:border-slate-800 dark:bg-slate-900"
                        >
                            <span
                                class="h-1.5 w-1.5 animate-bounce rounded-full bg-blue-600 [animation-delay:-0.3s]"
                            />
                            <span
                                class="h-1.5 w-1.5 animate-bounce rounded-full bg-blue-600 [animation-delay:-0.15s]"
                            />
                            <span
                                class="h-1.5 w-1.5 animate-bounce rounded-full bg-blue-600"
                            />
                        </div>
                        <span class="text-[11px] text-slate-400"
                            >AI sedang mengetik...</span
                        >
                    </div>
                </div>

                <!-- Chat Input Form -->
                <form
                    class="flex items-center gap-2 border-t border-slate-200 bg-white p-3 dark:border-slate-800 dark:bg-slate-900"
                    @submit.prevent="handleSend"
                >
                    <input
                        v-model="userInput"
                        type="text"
                        placeholder="Ketik pertanyaan seputar SOP / TA..."
                        class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:outline-hidden dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-100"
                    />
                    <button
                        type="submit"
                        :disabled="!userInput.trim()"
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-xs transition-all hover:bg-blue-700 disabled:pointer-events-none disabled:opacity-40"
                    >
                        <Send class="h-3.5 w-3.5" />
                    </button>
                </form>
            </div>
        </Transition>

        <!-- Floating Launcher Button -->
        <button
            type="button"
            class="group relative flex h-14 w-14 items-center justify-center rounded-2xl bg-linear-to-r from-blue-700 to-indigo-700 text-white shadow-xl shadow-blue-600/30 transition-all duration-300 hover:scale-105 active:scale-95"
            @click="isOpen = !isOpen"
        >
            <!-- Glow pulse animation -->
            <span
                class="absolute -inset-1 -z-10 animate-pulse rounded-2xl bg-blue-600 opacity-40 blur-md transition-all group-hover:opacity-75"
            />

            <X v-if="isOpen" class="h-6 w-6" />
            <div v-else class="flex flex-col items-center">
                <Bot class="h-6 w-6" />
            </div>

            <!-- Unread notification badge -->
            <span
                v-if="!isOpen"
                class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[9px] font-bold text-white ring-2 ring-white dark:ring-slate-900"
            >
                1
            </span>
        </button>
    </div>
</template>
