import type { Component } from 'vue';
import CatalogShelf from '@/components/bookshelf/shelves/CatalogShelf.vue';
import CozyShelf from '@/components/bookshelf/shelves/CozyShelf.vue';
import MinimalShelf from '@/components/bookshelf/shelves/MinimalShelf.vue';
import NeonShelf from '@/components/bookshelf/shelves/NeonShelf.vue';
import StackShelf from '@/components/bookshelf/shelves/StackShelf.vue';
import TimelineShelf from '@/components/bookshelf/shelves/TimelineShelf.vue';
import StoryShelf from '@/components/bookshelf/shelves/StoryShelf.vue';
import WoodShelf from '@/components/bookshelf/shelves/WoodShelf.vue';
import type { ShelfTheme } from '@/types';

/**
 * Setiap gaya rak diwakili satu komponen yang menerima props rak yang sama.
 */
export const shelfThemeComponents: Record<ShelfTheme, Component> = {
    wood: WoodShelf,
    minimal: MinimalShelf,
    neon: NeonShelf,
    cozy: CozyShelf,
    story: StoryShelf,
    catalog: CatalogShelf,
    stack: StackShelf,
    timeline: TimelineShelf,
};

/**
 * Ambil komponen rak untuk sebuah tema.
 */
export function shelfThemeComponent(theme: ShelfTheme): Component {
    return shelfThemeComponents[theme] ?? WoodShelf;
}

/**
 * Kelas latar halaman untuk tiap tema.
 */
export const shelfThemeSurface: Record<ShelfTheme, string> = {
    wood: 'bg-[#f8f3ea] dark:bg-[#15110d]',
    minimal: 'bg-white dark:bg-[#0b0b0c]',
    neon: 'bg-[#070a14]',
    cozy: 'bg-[#fff7f3] dark:bg-[#171110]',
    story: 'bg-[#0f0d18]',
    catalog: 'bg-[#efece3] dark:bg-[#14120d]',
    stack: 'bg-[#faf6ef] dark:bg-[#171310]',
    timeline: 'bg-[#f2f5f0] dark:bg-[#0e1512]',
};

/**
 * Kelas latar halaman untuk sebuah tema.
 */
export function shelfSurface(theme: ShelfTheme): string {
    return shelfThemeSurface[theme] ?? shelfThemeSurface.wood;
}
