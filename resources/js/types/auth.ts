import type { Role } from '@/types/models';

export type User = {
    id: number;
    name: string;
    email: string;
    username?: string;
    role?: Role | 'koordinator';
    nim_nip?: string;
    prodi?: string;
    jabatan?: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User | null;
};
