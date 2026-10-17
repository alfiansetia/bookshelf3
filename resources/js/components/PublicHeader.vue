<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, Library, Menu, Sprout } from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { cn } from '@/lib/utils';
import { dashboard, explore, home, login, register } from '@/routes';
import { index as booksIndex } from '@/routes/books';
import type { NavItem } from '@/types';

const page = usePage();
const user = computed(() => page.props.auth.user);
const open = ref(false);

const navItems: NavItem[] = [
    { title: 'Beranda', href: home() },
    { title: 'Jelajah rak', href: explore() },
];
</script>

<template>
    <header
        class="sticky top-0 z-40 border-b border-border/70 bg-background/85 backdrop-blur"
    >
        <div
            class="mx-auto flex h-16 w-full max-w-6xl items-center gap-3 px-4 sm:px-6"
        >
            <Link :href="home()" class="flex items-center gap-2">
                <span
                    class="flex size-9 items-center justify-center rounded-xl bg-primary text-primary-foreground"
                >
                    <Library class="size-5" />
                </span>
                <span class="font-display text-lg text-foreground">
                    {{ page.props.name }}
                </span>
            </Link>

            <nav class="ml-4 hidden items-center gap-1 md:flex">
                <Link
                    v-for="item in navItems"
                    :key="item.title"
                    :href="item.href"
                    :class="
                        cn(
                            'rounded-lg px-3 py-2 text-sm font-medium text-muted-foreground transition-colors hover:bg-muted hover:text-foreground',
                            page.url.startsWith('/jelajah') &&
                                item.title === 'Jelajah rak' &&
                                'bg-muted text-foreground',
                        )
                    "
                >
                    {{ item.title }}
                </Link>
            </nav>

            <div class="ml-auto flex items-center gap-2">
                <template v-if="user">
                    <Button
                        as-child
                        variant="ghost"
                        size="sm"
                        class="hidden sm:inline-flex"
                    >
                        <Link :href="booksIndex()">
                            <Library class="size-4" />
                            Rak saya
                        </Link>
                    </Button>
                    <Button as-child size="sm">
                        <Link :href="dashboard()">
                            <LayoutGrid class="size-4" />
                            Dashboard
                        </Link>
                    </Button>
                </template>

                <template v-else>
                    <Button
                        as-child
                        variant="ghost"
                        size="sm"
                        class="hidden sm:inline-flex"
                    >
                        <Link :href="login()">Masuk</Link>
                    </Button>
                    <Button as-child size="sm">
                        <Link :href="register()">
                            <Sprout class="size-4" />
                            Mulai gratis
                        </Link>
                    </Button>
                </template>

                <Sheet v-model:open="open">
                    <SheetTrigger as-child>
                        <Button
                            variant="ghost"
                            size="icon"
                            class="md:hidden"
                            aria-label="Buka menu"
                        >
                            <Menu class="size-5" />
                        </Button>
                    </SheetTrigger>
                    <SheetContent side="right" class="w-72 p-6">
                        <SheetTitle class="sr-only">Menu navigasi</SheetTitle>
                        <SheetHeader class="p-0 text-left">
                            <span class="font-display text-lg">
                                {{ page.props.name }}
                            </span>
                        </SheetHeader>

                        <nav class="mt-6 flex flex-col gap-1">
                            <Link
                                v-for="item in navItems"
                                :key="item.title"
                                :href="item.href"
                                class="rounded-lg px-3 py-2 text-sm font-medium text-muted-foreground hover:bg-muted hover:text-foreground"
                                @click="open = false"
                            >
                                {{ item.title }}
                            </Link>

                            <template v-if="user">
                                <Link
                                    :href="booksIndex()"
                                    class="rounded-lg px-3 py-2 text-sm font-medium text-muted-foreground hover:bg-muted hover:text-foreground"
                                    @click="open = false"
                                >
                                    Rak saya
                                </Link>
                                <Link
                                    :href="dashboard()"
                                    class="rounded-lg px-3 py-2 text-sm font-medium text-muted-foreground hover:bg-muted hover:text-foreground"
                                    @click="open = false"
                                >
                                    Dashboard
                                </Link>
                            </template>

                            <template v-else>
                                <Link
                                    :href="login()"
                                    class="rounded-lg px-3 py-2 text-sm font-medium text-muted-foreground hover:bg-muted hover:text-foreground"
                                    @click="open = false"
                                >
                                    Masuk
                                </Link>
                                <Link
                                    :href="register()"
                                    class="rounded-lg px-3 py-2 text-sm font-medium text-primary hover:bg-muted"
                                    @click="open = false"
                                >
                                    Mulai gratis
                                </Link>
                            </template>
                        </nav>
                    </SheetContent>
                </Sheet>
            </div>
        </div>
    </header>
</template>
