<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

import AcademicChatbot from '@/Components/AcademicChatbot.vue';
import MobileNav from '@/Layouts/partials/MobileNav.vue';
import Sidebar from '@/Layouts/partials/Sidebar.vue';
import Topbar from '@/Layouts/partials/Topbar.vue';

interface Props {
    title?: string;
}

withDefaults(defineProps<Props>(), {
    title: 'Dashboard',
});

const mobileMenuOpen = ref(false);
const sidebarCollapsed = ref(false);
</script>

<template>
    <div
        class="flex h-screen w-full overflow-hidden bg-[#F4F6FA] font-sans text-slate-800 antialiased transition-colors duration-200 dark:bg-[#080D1A] dark:text-slate-100"
    >
        <!-- 1. DESKTOP SIDEBAR -->
        <Sidebar
            :collapsed="sidebarCollapsed"
            @toggle-collapse="sidebarCollapsed = !sidebarCollapsed"
        />

        <!-- 2. MAIN APP CONTENT CONTAINER -->
        <div class="flex h-screen min-w-0 flex-1 flex-col overflow-hidden">
            <!-- TOP NAVBAR -->
            <Topbar
                :title="title"
                @toggle-mobile-menu="mobileMenuOpen = !mobileMenuOpen"
            />

            <!-- MOBILE NAV DRAWER -->
            <MobileNav :open="mobileMenuOpen" @close="mobileMenuOpen = false" />

            <!-- 3. SCROLLABLE CONTENT WRAPPER -->
            <div
                class="flex flex-1 flex-col justify-between overflow-x-hidden overflow-y-auto"
            >
                <!-- MAIN PAGE CONTENT BODY -->
                <main class="flex-1 p-4 lg:p-8">
                    <slot />
                </main>

                <!-- FOOTER -->
                <footer
                    class="shrink-0 border-t border-slate-200/80 bg-white/50 px-4 py-4 text-center text-xs text-slate-500 sm:px-8 dark:border-slate-800 dark:bg-[#0E1626]/50 dark:text-slate-400"
                >
                    <div
                        class="flex flex-col items-center justify-between gap-2 sm:flex-row"
                    >
                        <div class="flex items-center gap-4">
                            <Link
                                href="/panduan"
                                class="transition-colors hover:text-blue-600"
                            >
                                Panduan Layanan
                            </Link>
                            <span>•</span>
                            <Link
                                href="/prosedur"
                                class="transition-colors hover:text-blue-600"
                            >
                                SOP & Prosedur TA
                            </Link>
                        </div>
                        <p>© 2026 Program Studi Informatika ITK • SIPTA IF</p>
                    </div>
                </footer>
            </div>
        </div>

        <!-- 4. FLOATING ACADEMIC AI CHATBOT WIDGET -->
        <AcademicChatbot />
    </div>
</template>
