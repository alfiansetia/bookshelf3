import type { UserRole } from './auth';

/**
 * Satu baris pengguna pada halaman kelola pengguna admin.
 */
export type AdminUserRow = {
    id: number;
    name: string;
    username: string | null;
    email: string;
    avatar_url: string | null;
    role: UserRole;
    role_label: string;
    books_count: number;
    is_blocked: boolean;
    blocked_at: string | null;
    blocked_reason: string | null;
    blocked_by: string | null;
    shelf_url: string | null;
};

export type AdminUserStatusFilter = 'all' | 'active' | 'blocked';

export type AdminUserFilters = {
    search: string | null;
    status: AdminUserStatusFilter;
    role: UserRole | 'all';
};

export type AdminUserStats = {
    total: number;
    admins: number;
    blocked: number;
};

export type RoleOption = {
    value: UserRole;
    label: string;
};

export type ProfanityScanSummary = {
    users_scanned: number;
    books_scanned: number;
    user_hits: number;
    book_hits: number;
    words_count: number;
};

export type ProfanityUserFinding = {
    id: number;
    name: string;
    username: string | null;
    email: string;
    is_blocked: boolean;
    field: string;
    field_label: string;
    snippet: string;
    matched: string[];
    shelf_url: string | null;
};

export type ProfanityBookFinding = {
    id: number;
    title: string;
    field: string;
    field_label: string;
    snippet: string;
    matched: string[];
    owner_name: string | null;
    owner_username: string | null;
    shelf_url: string | null;
};
