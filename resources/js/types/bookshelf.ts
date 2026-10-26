export type BookStatus =
    | 'want_to_read'
    | 'reading'
    | 'finished'
    | 'on_hold'
    | 'dropped';

export type ShelfTheme =
    | 'wood'
    | 'minimal'
    | 'neon'
    | 'cozy'
    | 'story'
    | 'catalog'
    | 'stack';

export type ShelfVisibility = 'public' | 'unlisted' | 'private';

export type StatusColor = 'amber' | 'sky' | 'emerald' | 'violet' | 'rose';

export type Book = {
    id: number;
    title: string;
    slug: string;
    author: string;
    publisher: string | null;
    published_year: number | null;
    language: string;
    language_label: string;
    status: BookStatus;
    status_label: string;
    rating: number | null;
    synopsis: string | null;
    review: string | null;
    cover_url: string | null;
    cover_color: string | null;
    total_pages: number | null;
    started_at: string | null;
    finished_at: string | null;
    is_favorite: boolean;
    created_at: string | null;
    updated_at: string | null;
    public_url: string | null;
    owner?: {
        id: number;
        name: string;
        username: string | null;
        avatar_url: string | null;
    };
    can: {
        update: boolean;
        delete: boolean;
    };
};

export type BookFormValues = {
    title: string;
    author: string;
    publisher: string;
    published_year: string;
    language: string;
    status: BookStatus;
    rating: number | null;
    synopsis: string;
    review: string;
    cover_color: string | null;
    total_pages: string;
    started_at: string;
    finished_at: string;
    is_favorite: boolean;
    cover: File | null;
    remove_cover: boolean;
};

export type StatusOption = {
    value: BookStatus;
    label: string;
    description: string;
    icon: string;
    color: StatusColor;
};

export type ShelfThemeOption = {
    value: ShelfTheme;
    label: string;
    tagline: string;
    description: string;
    is_dark: boolean;
    accent: string;
};

export type LanguageOption = {
    value: string;
    label: string;
};

export type LanguageSummary = LanguageOption & {
    total: number;
};

export type SortOption = {
    value: string;
    label: string;
};

export type ShelfFilters = {
    search: string | null;
    status: BookStatus | null;
    language: string | null;
    sort: string;
    favorite: boolean;
};

/**
 * Kartu buku ramping untuk rak publik: cukup untuk kartu, filter,
 * dan spotlight di frontend tanpa sinopsis/review yang berat.
 */
export type ShelfBook = Pick<
    Book,
    | 'id'
    | 'title'
    | 'author'
    | 'published_year'
    | 'language'
    | 'language_label'
    | 'status'
    | 'status_label'
    | 'rating'
    | 'cover_url'
    | 'cover_color'
    | 'is_favorite'
    | 'created_at'
    | 'finished_at'
    | 'public_url'
>;

export type ShelfSummary = {
    total: number;
    finished: number;
    reading: number;
    want_to_read: number;
    on_hold: number;
    dropped: number;
    favorites: number;
    pages_read: number;
    average_rating: number;
};

export type ShelfProfile = {
    name: string;
    username: string;
    avatar_url: string | null;
    tagline: string | null;
    bio: string | null;
    theme: ShelfTheme;
    visibility: ShelfVisibility;
    is_public: boolean;
    socials: SocialProfile[];
    member_since: string | null;
    joined_at: string | null;
};

export type ShelfVisibilityOption = {
    value: ShelfVisibility;
    label: string;
    description: string;
};

export type SocialProfile = {
    platform: string;
    label: string;
    icon: string;
    handle: string;
    url: string;
};

export type SocialPlatformOption = {
    value: string;
    label: string;
    icon: string;
    placeholder: string;
    username_only: boolean;
    max_length: number;
};

export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: PaginationLink[];
};

export type FeaturedShelf = {
    name: string;
    username: string;
    avatar_url: string | null;
    tagline: string | null;
    theme: ShelfTheme;
    theme_label: string;
    books_count: number;
    covers: string[];
};

export type ShelfCard = {
    name: string;
    username: string;
    avatar_url: string | null;
    tagline: string | null;
    theme: ShelfTheme;
    theme_label: string;
    books_count: number;
    finished_count: number;
};
