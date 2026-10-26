export type UserRole = 'admin' | 'user';

export type User = {
    id: number;
    name: string;
    email: string;
    username: string | null;
    role: UserRole;
    avatar?: string;
    avatar_url: string | null;
    tagline: string | null;
    bio: string | null;
    theme: 'wood' | 'minimal' | 'neon' | 'cozy' | 'story';
    is_public: boolean;
    blocked_at: string | null;
    blocked_reason: string | null;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};
