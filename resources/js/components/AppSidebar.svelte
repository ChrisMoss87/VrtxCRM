<script lang="ts">
    import NavFooter from '@/components/NavFooter.svelte';
    import NavMain from '@/components/NavMain.svelte';
    import NavUser from '@/components/NavUser.svelte';
    import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
    import { dashboard } from '@/routes';
    import { type NavItem } from '@/types';
    import { Link, page } from '@inertiajs/svelte';
    import { BookOpen, Folder, LayoutGrid, Users, Building, DollarSign, Briefcase, Settings, Database } from 'lucide-svelte';
    import AppLogo from './AppLogo.svelte';

    // Icon mapping for modules
    const moduleIconMap: Record<string, any> = {
        users: Users,
        building: Building,
        'dollar-sign': DollarSign,
        briefcase: Briefcase,
    };

    // Get modules from shared Inertia data
    const modules = $page.props.modules as Array<{id: number, name: string, api_name: string, icon: string}> || [];

    const mainNavItems: NavItem[] = [
        {
            title: 'Dashboard',
            href: '/',
            icon: LayoutGrid,
        },
        ...modules.map(module => ({
            title: module.name,
            href: `/modules/${module.api_name}`,
            icon: moduleIconMap[module.icon] || Folder,
        })),
    ];

    const adminNavItems: NavItem[] = [
        {
            title: 'Module Builder',
            href: '/admin/modules',
            icon: Database,
        },
        {
            title: 'Settings',
            href: '/settings',
            icon: Settings,
        },
    ];

    const footerNavItems: NavItem[] = [

    ];
</script>

<Sidebar collapsible="icon" variant="inset">
    <SidebarHeader>
        <SidebarMenu>
            <SidebarMenuItem>
                <SidebarMenuButton size="lg">
                    <Link href={dashboard()}>
                        <AppLogo />
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarHeader>

    <SidebarContent>
        <NavMain items={mainNavItems} />

        <!-- Admin Section -->
        <div class="px-2 py-0">
            <div class="px-2 py-1.5 text-xs font-semibold text-sidebar-foreground/70">
                Admin
            </div>
            <div class="space-y-0.5">
                {#each adminNavItems as item (item.title)}
                    <Link href={item.href} class="block w-full">
                        <button
                            class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-sm font-medium text-sidebar-foreground/70 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground transition-colors"
                            class:bg-sidebar-accent={item.href === $page.url}
                            class:text-sidebar-accent-foreground={item.href === $page.url}
                        >
                            {#if item.icon}
                                {@const Icon = item.icon}
                                <Icon class="h-4 w-4 shrink-0" />
                            {/if}
                            <span>{item.title}</span>
                        </button>
                    </Link>
                {/each}
            </div>
        </div>
    </SidebarContent>

    <SidebarFooter>
        <NavFooter items={footerNavItems} class="mt-auto" />
        <NavUser />
    </SidebarFooter>
</Sidebar>
