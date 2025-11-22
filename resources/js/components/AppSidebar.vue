<script setup lang="ts">
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
import { dashboard } from '@/routes';
import type { AppPageProps, NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Users, DollarSign, Link as LinkIcon, Settings, Stethoscope, Shield } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { computed } from 'vue';

const page = usePage<AppPageProps>();

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];

    const auth = page.props.auth;
    const user = auth.user;

    const isStaffOrAdmin =
        auth.isStaffOrAdmin ?? (user && ((user.roles && user.roles.some((r: any) => r.name === 'admin')) || (user.roles && user.roles.some((r: any) => r.name === 'staff'))));

    const isAgent = user && (user.role === 'agent' || (user.roles && user.roles.some((r: any) => r.name === 'agent')));

    if (isStaffOrAdmin) {
        items.push({
            title: 'Patients',
            href: '/dashboard/patients',
            icon: Users,
        });
    }

    // Clinical & Compliance items (available to all authenticated users)
    items.push({
        title: 'Clinical',
        href: '/clinical/questionnaires',
        icon: Stethoscope,
    });

    items.push({
        title: 'Compliance',
        href: '/compliance/dashboard',
        icon: Shield,
    });

    const isAdmin = user && (user.roles && user.roles.some((r: any) => r.name === 'admin'));
    if (isAdmin) {
        items.push({
            title: 'Subscription Config',
            href: '/admin/subscription-configuration',
            icon: Settings,
        });
    }

    if (isAgent) {
        items.push({
            title: 'Commission Dashboard',
            href: '/agent/commission/dashboard',
            icon: DollarSign,
        });
        items.push({
            title: 'Referral Links',
            href: '/agent/referral-links',
            icon: LinkIcon,
        });
    }

    return items;
});

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
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
