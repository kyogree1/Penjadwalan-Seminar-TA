/**
 * SIPTA IF ITK - Unified Top Navbar Component (Desk Bar)
 * Standardized across ALL pages for consistent height, spacing, search & user profile.
 */
window.AppNavbar = {
  name: 'AppNavbar',
  props: {
    showSearch: {
      type: Boolean,
      default: true
    },
    searchPlaceholder: {
      type: String,
      default: 'Cari mahasiswa, NIM, atau topik skripsi...'
    },
    modelValue: {
      type: String,
      default: ''
    },
    userName: {
      type: String,
      default: 'Dra. Nurul Rahmania, M.T.'
    },
    userRole: {
      type: String,
      default: 'Koorprodi • Dosen IF'
    },
    userInitials: {
      type: String,
      default: 'NR'
    },
    activeSemester: {
      type: String,
      default: 'Gasal 2026/2027 • Gel. 2'
    },
    isDark: {
      type: Boolean,
      default: true
    }
  },
  emits: ['toggle-sidebar', 'toggle-theme', 'update:modelValue'],
  template: `
    <header class="app-navbar-header">
      
      <!-- Left Section: Hamburger Button & Search Bar -->
      <div class="navbar-left-group">
        <!-- Mobile/Desktop Sidebar Hamburger Toggle -->
        <button 
          @click="$emit('toggle-sidebar')"
          class="navbar-icon-btn"
          title="Buka / Tutup Sidebar"
          type="button"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>

        <!-- Search Input Bar -->
        <div v-if="showSearch" class="navbar-search-wrapper">
          <svg class="navbar-search-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <input 
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            type="text" 
            :placeholder="searchPlaceholder"
            class="navbar-search-input"
          />
        </div>
      </div>

      <!-- Right Section: Semester Badge, Dark Mode, Profile -->
      <div class="navbar-right-group">
        <!-- Active Academic Semester Badge -->
        <div class="semester-pill">
          <span class="pulse-dot"></span>
          <span class="semester-text">{{ activeSemester }}</span>
        </div>

        <!-- Dark/Light Mode Toggle -->
        <button 
          @click="$emit('toggle-theme')" 
          class="navbar-icon-btn"
          :title="isDark ? 'Mode Terang' : 'Mode Gelap'"
          type="button"
        >
          <svg v-if="isDark" class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
          </svg>
          <svg v-else class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
          </svg>
        </button>

        <!-- User Profile Pill -->
        <div class="navbar-user-profile">
          <div class="user-avatar-box">
            {{ userInitials }}
          </div>
          <div class="user-info-text">
            <span class="user-name">{{ userName }}</span>
            <span class="user-role">{{ userRole }}</span>
          </div>
        </div>
      </div>
    </header>
  `
};
