/**
 * SIPTA IF ITK - Reusable Floating AI Chatbot Component
 * Anchored permanently at bottom-right corner across all pages via CSS.
 */
window.AppChatbot = {
  name: 'AppChatbot',
  setup() {
    const isChatOpen = Vue.ref(false);
    const chatInput = Vue.ref('');
    const chatMessages = Vue.ref([
      {
        sender: 'bot',
        text: 'Halo! Saya SIPTA Assistant berbasis LLM. Ada yang bisa saya bantu seputar pengajuan judul, validasi berkas sempro, atau jadwal sidang?',
        time: '08:00'
      }
    ]);

    const sendChatMessage = () => {
      if (!chatInput.value.trim()) return;
      const userText = chatInput.value;
      chatMessages.value.push({
        sender: 'user',
        text: userText,
        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
      });
      chatInput.value = '';

      setTimeout(() => {
        let reply = "Pertanyaan Anda telah kami catat. Informasi administrasi dan format berkas Tugas Akhir dapat dilihat di panduan akademik prodi Informatika ITK.";
        const lower = userText.toLowerCase();

        if (lower.includes('syarat') || lower.includes('berkas') || lower.includes('sempro')) {
          reply = "Syarat administrasi Sempro meliputi: 1) Draf Proposal Bab 1-3 yang di-ACC Pembimbing, 2) Bukti Turnitin maksimal 25%, 3) Log Bimbingan minimal 8 kali, dan 4) Transkrip Nilai Sementara.";
        } else if (lower.includes('jadwal') || lower.includes('gelombang')) {
          reply = "Jadwal Seminar Proposal Gelombang 2 dilaksanakan pada 28 - 30 September 2026. Batas akhir pengumpulan berkas adalah 25 September 2026.";
        } else if (lower.includes('ai') || lower.includes('plotting') || lower.includes('genetika') || lower.includes('ga')) {
          reply = "Plotting penguji otomatis menggunakan Algoritma Genetika (GA) dengan mencocokkan kemiripan KBK dosen (Random Forest) dan mengoptimalkan batasan kuota beban bimbingan tanpa bentrok jadwal.";
        } else if (lower.includes('judul')) {
          reply = "Pengajuan judul baru memerlukan abstrak awal, usulan 2 dosen pembimbing, dan pemilihan KBK (AI, Software Engineering, atau Jaringan).";
        }

        chatMessages.value.push({
          sender: 'bot',
          text: reply,
          time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
        });
      }, 500);
    };

    const sendQuickPrompt = (promptText) => {
      chatInput.value = promptText;
      sendChatMessage();
    };

    return {
      isChatOpen,
      chatInput,
      chatMessages,
      sendChatMessage,
      sendQuickPrompt
    };
  },
  template: `
    <div class="sipta-chatbot-fixed-container">
      <!-- Chat Dialog Box -->
      <div v-if="isChatOpen" class="sipta-chatbot-window">
        <!-- Header -->
        <div class="sipta-chatbot-header">
          <div class="flex items-center gap-2">
            <span class="text-base">🤖</span>
            <div>
              <h3 class="text-xs font-bold leading-tight text-white">SIPTA Assistant</h3>
              <p class="text-[10px] text-blue-200">LLM Academic Support</p>
            </div>
          </div>
          <button @click="isChatOpen = false" class="text-white/80 hover:text-white p-1 text-sm rounded-lg hover:bg-white/10" type="button">✕</button>
        </div>

        <!-- Chat Messages Area -->
        <div class="sipta-chatbot-messages">
          <div v-for="(msg, idx) in chatMessages" :key="idx" :class="msg.sender === 'user' ? 'flex justify-end' : 'flex justify-start'">
            <div :class="msg.sender === 'user' ? 'chatbot-bubble-user' : 'chatbot-bubble-bot'">
              <p class="leading-relaxed">{{ msg.text }}</p>
              <span class="text-[9px] opacity-60 mt-1 block text-right">{{ msg.time }}</span>
            </div>
          </div>
        </div>

        <!-- Quick Prompt Suggestions -->
        <div class="sipta-chatbot-quick-prompts">
          <button @click="sendQuickPrompt('Apa saja syarat berkas Sempro?')" class="quick-prompt-btn" type="button">📄 Syarat Sempro</button>
          <button @click="sendQuickPrompt('Kapan jadwal Sempro Gelombang 2?')" class="quick-prompt-btn" type="button">📅 Jadwal Gel. 2</button>
          <button @click="sendQuickPrompt('Bagaimana cara kerja AI plotting?')" class="quick-prompt-btn" type="button">⚡ AI Plotting</button>
        </div>

        <!-- Input Bar -->
        <form @submit.prevent="sendChatMessage" class="sipta-chatbot-input-bar">
          <input v-model="chatInput" type="text" placeholder="Tanya seputar sempro/TA..." class="chatbot-input" />
          <button type="submit" class="chatbot-send-btn" title="Kirim">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
          </button>
        </form>
      </div>

      <!-- Floating Launcher Button -->
      <button @click="isChatOpen = !isChatOpen" class="sipta-chatbot-trigger" type="button">
        <span class="pulse-indicator"></span>
        <span class="text-sm">🤖</span>
        <span class="text-xs font-extrabold tracking-tight">Tanya SIPTA AI</span>
      </button>
    </div>
  `
};
