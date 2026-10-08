/**
 * SIPTA IF ITK - Reusable Sidebar Component
 * Standardized navigation, robust sizing, and high-contrast active states.
 */
window.AppSidebar = {
  name: 'AppSidebar',
  props: {
    activePage: {
      type: String,
      default: 'dashboard'
    },
    isCollapsed: {
      type: Boolean,
      default: false
    },
    isMobileOpen: {
      type: Boolean,
      default: false
    },
    rootPath: {
      type: String,
      default: ''
    }
  },
  emits: ['toggle-collapse', 'close-mobile'],
  setup(props) {
    const isDataPengajuanOpen = Vue.ref(true);
    const isJadwalOpen = Vue.ref(true);

    const toggleDataPengajuan = () => {
      isDataPengajuanOpen.value = !isDataPengajuanOpen.value;
    };

    const toggleJadwal = () => {
      isJadwalOpen.value = !isJadwalOpen.value;
    };

    return {
      isDataPengajuanOpen,
      isJadwalOpen,
      toggleDataPengajuan,
      toggleJadwal
    };
  },
  template: `
    <div>
      <!-- MOBILE BACKDROP OVERLAY -->
      <div 
        v-if="isMobileOpen" 
        @click="$emit('close-mobile')"
        class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm lg:hidden transition-opacity"
      ></div>

      <!-- SIDEBAR ASIDE -->
      <aside 
        :class="[
          isMobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
          isCollapsed ? 'is-collapsed' : '',
          'app-sidebar-aside w-64 fixed lg:sticky top-0 inset-y-0 left-0 z-50 lg:z-30 flex flex-col bg-white dark:bg-[#111827] border-r border-slate-200/80 dark:border-slate-800/80 transition-all duration-300 shadow-lg lg:shadow-none h-screen flex-shrink-0'
        ]"
      >
        <!-- Brand Logo Header -->
        <div class="h-[72px] flex items-center justify-between px-4 border-b border-slate-100 dark:border-slate-800 flex-shrink-0">
          <a :href="rootPath + 'index.html'" class="flex items-center gap-3 overflow-hidden">
            <div class="brand-logo-box rounded-xl bg-brand-50 flex items-center justify-center p-2 shadow-md shadow-brand-900/20 flex-shrink-0" style="width: 40px; height: 40px; min-width: 40px; min-height: 40px;">
              <img :src="rootPath + 'assets/images/logo_informatika.png'" alt="Logo Informatika ITK" class="brand-logo-img" style="max-width: 100%; max-height: 100%; object-fit: contain;" />
            </div>
            <div v-show="!isCollapsed" class="flex flex-col overflow-hidden">
              <span class="text-base font-extrabold tracking-tight text-brand-900 dark:text-white leading-tight">SIPTA <span class="text-brand-600 dark:text-blue-400">IF</span></span>
              <span class="text-[11px] font-medium text-slate-400 truncate">Informatika ITK</span>
            </div>
          </a>

          <!-- Mobile Close button (X) -->
          <button 
            @click="$emit('close-mobile')" 
            class="lg:hidden p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
            type="button"
          >
            ✕
          </button>
        </div>

        <!-- Navigation Links Container -->
        <div class="flex-1 overflow-y-auto px-3 py-4 space-y-5">
          
          <!-- SECTION 1: MANAJEMEN UTAMA -->
          <div>
            <p v-show="!isCollapsed" class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2">
              Manajemen Utama
            </p>
            <ul class="space-y-1">
              <!-- Dashboard -->
              <li>
                <a 
                  :href="rootPath + 'index.html'" 
                  class="sidebar-nav-link"
                  :class="{ active: activePage === 'dashboard' }"
                  :title="isCollapsed ? 'Dashboard' : ''"
                >
                  <svg width="20" height="20" class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                  </svg>
                  <span v-show="!isCollapsed" class="truncate">Dashboard</span>
                </a>
              </li>

              <!-- Log Bimbingan -->
              <li>
                <a 
                  :href="rootPath + 'pages/bimbingan.html'" 
                  class="sidebar-nav-link"
                  :class="{ active: activePage === 'bimbingan' }"
                  :title="isCollapsed ? 'Log Bimbingan' : ''"
                >
                  <svg width="20" height="20" class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                  </svg>
                  <span v-show="!isCollapsed" class="truncate">Log Bimbingan</span>
                </a>
              </li>
            </ul>
          </div>

          <!-- SECTION 2: DATA PENGAJUAN (ACCORDION) -->
          <div>
            <div 
              @click="toggleDataPengajuan"
              class="flex items-center justify-between px-3 py-1.5 cursor-pointer text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 select-none"
            >
              <span v-show="!isCollapsed" class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                Data Pengajuan
              </span>
              <svg 
                v-show="!isCollapsed"
                class="w-3.5 h-3.5 transition-transform duration-200" 
                :class="{ 'rotate-90': isDataPengajuanOpen }" 
                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
              </svg>
            </div>

            <ul v-show="isDataPengajuanOpen || isCollapsed" class="space-y-1 mt-1">
              <!-- Daftar Judul -->
              <li>
                <a 
                  :href="rootPath + 'pages/pengajuan-judul.html'" 
                  class="sidebar-nav-link"
                  :class="{ active: activePage === 'judul' }"
                  :title="isCollapsed ? 'Daftar Judul' : ''"
                >
                  <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                  <span v-show="!isCollapsed" class="truncate">Daftar Judul</span>
                  <span v-show="!isCollapsed" class="ml-auto bg-amber-500/20 text-amber-400 border border-amber-500/30 text-xs font-bold px-1.5 py-0.2 rounded-full">3</span>
                </a>
              </li>

              <!-- Bimbingan -->
              <li>
                <a 
                  :href="rootPath + 'pages/bimbingan.html'" 
                  class="sidebar-nav-link"
                  :class="{ active: activePage === 'bimbingan' }"
                  :title="isCollapsed ? 'Bimbingan TA' : ''"
                >
                  <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                  </svg>
                  <span v-show="!isCollapsed" class="truncate">Bimbingan</span>
                </a>
              </li>

              <!-- Seminar Proposal -->
              <li>
                <a 
                  :href="rootPath + 'pages/pengajuan-sempro.html'" 
                  class="sidebar-nav-link"
                  :class="{ active: activePage === 'sempro' }"
                  :title="isCollapsed ? 'Seminar Proposal' : ''"
                >
                  <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                  </svg>
                  <span v-show="!isCollapsed" class="truncate">Seminar Proposal</span>
                  <span v-show="!isCollapsed" class="ml-auto bg-amber-500/20 text-amber-400 border border-amber-500/30 text-[10px] font-extrabold px-1.5 py-0.2 rounded-full">2</span>
                </a>
              </li>

              <!-- Sidang TA -->
              <li>
                <a 
                  :href="rootPath + 'pages/pengajuan-sidang.html'" 
                  class="sidebar-nav-link"
                  :class="{ active: activePage === 'sidang' }"
                  :title="isCollapsed ? 'Sidang TA' : ''"
                >
                  <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                  </svg>
                  <span v-show="!isCollapsed" class="truncate">Sidang TA</span>
                  <span v-show="!isCollapsed" class="ml-auto bg-purple-500/20 text-purple-400 border border-purple-500/30 text-[10px] font-extrabold px-1.5 py-0.2 rounded-full">1</span>
                </a>
              </li>
            </ul>
          </div>

          <!-- SECTION 3: JADWAL & AI PLOTTING (ACCORDION) -->
          <div>
            <div 
              @click="toggleJadwal"
              class="flex items-center justify-between px-3 py-1.5 cursor-pointer text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 select-none"
            >
              <span v-show="!isCollapsed" class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                Jadwal & AI Plotting
              </span>
              <svg 
                v-show="!isCollapsed"
                class="w-3.5 h-3.5 transition-transform duration-200" 
                :class="{ 'rotate-90': isJadwalOpen }" 
                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
              </svg>
            </div>

            <ul v-show="isJadwalOpen || isCollapsed" class="space-y-1 mt-1">
              <!-- Penentuan Jadwal & Plotting Penguji (AI GA) -->
              <li>
                <a 
                  :href="rootPath + 'pages/penentuan-jadwal.html'" 
                  class="sidebar-nav-link"
                  :class="{ active: activePage === 'plotting' }"
                  :title="isCollapsed ? 'Plotting Jadwal AI' : ''"
                >
                  <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                  <span v-show="!isCollapsed" class="truncate">Plotting Jadwal (GA)</span>
                  <span v-show="!isCollapsed" class="ml-auto bg-blue-500/20 text-blue-400 border border-blue-500/30 text-[10px] font-extrabold px-1.5 py-0.2 rounded-full">AI</span>
                </a>
              </li>

              <!-- List Gelombang -->
              <li>
                <a 
                  :href="rootPath + 'pages/jadwal-sempro.html'" 
                  class="sidebar-nav-link"
                  :class="{ active: activePage === 'gelombang' }"
                  :title="isCollapsed ? 'Periode Gelombang' : ''"
                >
                  <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  <span v-show="!isCollapsed" class="truncate">Periode Gelombang</span>
                </a>
              </li>
            </ul>
          </div>

          <!-- SECTION 4: DATA MASTER -->
          <div>
            <p v-show="!isCollapsed" class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2">
              Data Master
            </p>
            <ul class="space-y-1">
              <li>
                <a 
                  :href="rootPath + 'pages/data-dosen.html'" 
                  class="sidebar-nav-link"
                  :class="{ active: activePage === 'dosen' }"
                  :title="isCollapsed ? 'Data Dosen' : ''"
                >
                  <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                  </svg>
                  <span v-show="!isCollapsed" class="truncate">Data Dosen</span>
                </a>
              </li>
              <li>
                <a 
                  :href="rootPath + 'pages/data-mahasiswa.html'" 
                  class="sidebar-nav-link"
                  :class="{ active: activePage === 'mahasiswa' }"
                  :title="isCollapsed ? 'Data Mahasiswa' : ''"
                >
                  <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                  </svg>
                  <span v-show="!isCollapsed" class="truncate">Data Mahasiswa</span>
                </a>
              </li>
            </ul>
          </div>

          <!-- SECTION 5: PROSEDUR & KATALOG -->
          <div>
            <p v-show="!isCollapsed" class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2">
              Prosedur & Katalog
            </p>
            <ul class="space-y-1">
              <li>
                <a 
                  :href="rootPath + 'pages/katalog-ta.html'" 
                  class="sidebar-nav-link"
                  :class="{ active: activePage === 'katalog' }"
                  :title="isCollapsed ? 'Katalog TA' : ''"
                >
                  <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                  </svg>
                  <span v-show="!isCollapsed" class="truncate">Katalog TA</span>
                  <span v-show="!isCollapsed" class="ml-auto bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[10px] font-extrabold px-1.5 py-0.2 rounded-full">Arsip</span>
                </a>
              </li>
            </ul>
          </div>
        </div>

        <!-- Toggle Desktop Collapse Button -->
        <div class="p-3 border-t border-slate-100 dark:border-slate-800 hidden lg:block flex-shrink-0">
          <button 
            @click="$emit('toggle-collapse')"
            class="w-full flex items-center justify-center p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
            :title="isCollapsed ? 'Perbesar Sidebar' : 'Kecilkan Sidebar'"
            type="button"
          >
            <svg class="w-5 h-5 transition-transform duration-200" :class="{ 'rotate-180': isCollapsed }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
            </svg>
          </button>
        </div>
      </aside>
    </div>
  `
};
