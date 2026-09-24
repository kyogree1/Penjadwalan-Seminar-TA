import { ref } from 'vue';

const isDarkMode = ref(false);

const applyTheme = (dark: boolean) => {
    isDarkMode.value = dark;
    document.documentElement.classList.toggle('dark', dark);

    try {
        localStorage.setItem('theme', dark ? 'dark' : 'light');
    } catch {}
};

const initializeTheme = () => {
    let savedTheme: string | null = null;

    try {
        savedTheme = localStorage.getItem('theme');
    } catch {}

    applyTheme(
        savedTheme === 'dark' ||
            (!savedTheme &&
                window.matchMedia('(prefers-color-scheme: dark)').matches),
    );
};

const toggleDarkMode = () => applyTheme(!isDarkMode.value);

export const useTheme = () => ({
    initializeTheme,
    isDarkMode,
    toggleDarkMode,
});
