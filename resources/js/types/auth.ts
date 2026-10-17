export type User = {
    id: number;
    name: string;
    email: string;
    username: string | null;
    avatar?: string;
    avatar_url: string | null;
    tagline: string | null;
    bio: string | null;
    theme: 'wood' | 'minimal' | 'neon' | 'cozy';
    is_public: boolean;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};
