<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    Compass,
    LayoutDashboard,
    LayoutGrid,
    Library,
    Plus,
    ScanSearch,
    ShieldCheck,
    Sparkles,
} from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard, explore } from '@/routes';
import { index as profanityIndex } from '@/routes/admin/profanity';
import { index as adminUsersIndex } from '@/routes/admin/users';
import { create, index as booksIndex } from '@/routes/books';
import { edit as editShelf } from '@/routes/shelf';
import type { NavItem } from '@/types';

const page = usePage();

const isAdmin = computed(() => page.props.auth.user.role === 'admin');

const mainNavItems = computed<NavItem[]>(() => [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Rak saya',
        href: booksIndex(),
        icon: Library,
    },
    {
        title: 'Tambah buku',
        href: create(),
        icon: Plus,
    },
    {
        title: 'Gaya rak',
        href: editShelf(),
        icon: Sparkles,
    },
    {
        title: 'Jelajah rak',
        href: explore(),
        icon: Compass,
    },
]);

// Menu admin dipisah grup sendiri dengan label pembatas.
const adminNavItems = computed<NavItem[]>(() =>
    isAdmin.value
        ? [
              {
                  title: 'Dashboard Admin',
                  href: '/admin',
                  icon: LayoutDashboard,
              },
              {
                  title: 'Kelola pengguna',
                  href: adminUsersIndex(),
                  icon: ShieldCheck,
              },
              {
                  title: 'Scan kata kasar',
                  href: profanityIndex(),
                  icon: ScanSearch,
              },
          ]
        : [],
);

const footerNavItems: NavItem[] = [
    {
        title: 'Rak publik',
        href: explore(),
        icon: Compass,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" label="Platform" />
            <NavMain v-if="isAdmin" :items="adminNavItems" label="Admin" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
