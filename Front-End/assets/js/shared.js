/**
 * SIPTA IF ITK - Shared Frontend Utilities & Layout Composable
 */

// Theme Management Helper
const ThemeManager = {
  getSavedTheme() {
    try {
      return localStorage.getItem('theme');
    } catch (e) {
      return null;
    }
  },
  setSavedTheme(val) {
    try {
      localStorage.setItem('theme', val);
    } catch (e) {}
  },
  init(isDarkRef) {
    const saved = this.getSavedTheme();
    // Default to dark mode to match Capstone dark UI design
    const isDark = saved ? saved === 'dark' : true;
    if (isDark) {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
    if (isDarkRef) {
      isDarkRef.value = isDark;
    }
    return isDark;
  },

  toggle(isDarkRef) {
    const nextVal = !isDarkRef.value;
    isDarkRef.value = nextVal;
    if (nextVal) {
      document.documentElement.classList.add('dark');
      this.setSavedTheme('dark');
    } else {
      document.documentElement.classList.remove('dark');
      this.setSavedTheme('light');
    }
    return nextVal;
  }
};

// Common Alert Helper
const alertInfo = (msg) => {
  alert(msg);
};

// Composable for Layout & Navbar State
function useLayout() {
  const isSidebarCollapsed = Vue.ref(false);
  const isMobileSidebarOpen = Vue.ref(false);
  const isDark = Vue.ref(false);

  const toggleSidebar = () => {
    if (window.innerWidth < 1024) {
      isMobileSidebarOpen.value = !isMobileSidebarOpen.value;
    } else {
      isSidebarCollapsed.value = !isSidebarCollapsed.value;
    }
  };

  const toggleDesktopSidebar = () => {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
  };

  const closeMobileSidebar = () => {
    isMobileSidebarOpen.value = false;
  };

  const toggleTheme = () => {
    ThemeManager.toggle(isDark);
  };

  Vue.onMounted(() => {
    ThemeManager.init(isDark);
  });

  return {
    isSidebarCollapsed,
    isMobileSidebarOpen,
    isDark,
    toggleSidebar,
    toggleDesktopSidebar,
    closeMobileSidebar,
    toggleTheme
  };
}

// Global Components Registration Helper
function registerComponents(app) {
  if (window.AppSidebar) {
    app.component('app-sidebar', window.AppSidebar);
  }
  if (window.AppNavbar) {
    app.component('app-navbar', window.AppNavbar);
  }
  if (window.AppChatbot) {
    app.component('app-chatbot', window.AppChatbot);
  }
}
