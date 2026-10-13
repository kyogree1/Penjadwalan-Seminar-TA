import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import type { User } from '@/types/auth';
import type { Role } from '@/types/models';

export const useAuth = () => {
    const page = usePage();

    const user = computed<User | null>(
        () => (page.props.auth as { user: User | null })?.user ?? null,
    );

    const role = computed<Role>(() => {
        const r = user.value?.role;
        if (r === 'koordinator' || r === 'kaprodi') return 'kaprodi';
        if (r === 'dosen') return 'dosen';
        if (r === 'tendik') return 'tendik';
        return 'mahasiswa';
    });

    const roleInfo = computed(() => {
        switch (role.value) {
            case 'dosen':
                return {
                    title: 'Portal Dosen',
                    badge: 'Dosen Pembimbing & Penguji',
                    color: 'bg-indigo-500/10 text-indigo-400 border-indigo-500/30',
                    homePath: '/dosen/dashboard',
                };
            case 'kaprodi':
                return {
                    title: 'Portal Kaprodi',
                    badge: 'Koordinator Program Studi',
                    color: 'bg-purple-500/10 text-purple-400 border-purple-500/30',
                    homePath: '/kaprodi/dashboard',
                };
            case 'tendik':
                return {
                    title: 'Portal Tendik',
                    badge: 'Tenaga Kependidikan & Admin',
                    color: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                    homePath: '/tendik/dashboard',
                };
            case 'mahasiswa':
            default:
                return {
                    title: 'Portal Mahasiswa',
                    badge: 'Mahasiswa S1 Informatika',
                    color: 'bg-blue-500/10 text-blue-400 border-blue-500/30',
                    homePath: '/dashboard',
                };
        }
    });

    const profileData = computed(() => {
        const u = user.value;
        if (!u) {
            return {
                name: 'Tamu / Pengguna',
                idNumber: 'Sistem Terbuka',
                initials: 'TP',
                roleLabel: roleInfo.value.badge,
            };
        }

        const initials = u.name
            ? u.name
                  .split(' ')
                  .filter(Boolean)
                  .slice(0, 2)
                  .map((w) => w[0]?.toUpperCase())
                  .join('')
            : 'US';

        return {
            name: u.name,
            idNumber: u.nim_nip || u.username || u.email,
            initials: initials || 'US',
            roleLabel: u.jabatan || roleInfo.value.badge,
        };
    });

    return {
        user,
        role,
        roleInfo,
        profileData,
    };
};
