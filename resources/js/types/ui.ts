export type Appearance = 'light' | 'dark' | 'system';
export type ResolvedAppearance = 'light' | 'dark';

export type AppVariant = 'header' | 'sidebar';

export type FlashToast = {
    type: 'success' | 'info' | 'warning' | 'error';
    message: string;
};

export type SeoDefaults = {
    siteName: string;
    description: string;
    image: string;
    locale: string;
    twitter: string | null;
};
