<script lang="ts">
    import NavFooter from '@/components/NavFooter.svelte';
    import NavMain from '@/components/NavMain.svelte';
    import NavUser from '@/components/NavUser.svelte';
    import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
    import { dashboard } from '@/routes';
    import { type NavItem } from '@/types';
    import { Link, page } from '@inertiajs/svelte';
    import { BookOpen, Folder, LayoutGrid, Users, Building, DollarSign, Briefcase } from 'lucide-svelte';
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
    </SidebarContent>

    <SidebarFooter>
        <NavFooter items={footerNavItems} class="mt-auto" />
        <NavUser />
    </SidebarFooter>
</Sidebar>
