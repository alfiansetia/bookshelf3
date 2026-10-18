<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Compass, LayoutGrid, Library, Plus, ShieldCheck } from '@lucide/vue';
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
import { index as adminUsersIndex } from '@/routes/admin/users';
import { create, index as booksIndex } from '@/routes/books';
import type { NavItem } from '@/types';

const page = usePage();

// Menu admin hanya muncul untuk pengguna berperan admin.
const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
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
            title: 'Jelajah rak',
            href: explore(),
            icon: Compass,
        },
    ];

    if (page.props.auth.user.role === 'admin') {
        items.push({
            title: 'Kelola pengguna',
            href: adminUsersIndex(),
            icon: ShieldCheck,
        });
    }

    return items;
});

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
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
