import {
    Building2,
    House,
    Inbox,
    LayoutDashboard,
    type LucideIcon,
    Mail,
    Package,
    ScrollText,
    Settings,
    Shield,
    UserCog,
    Users,
    Wrench,
} from 'lucide-react';

const icons: Record<string, LucideIcon> = {
    home: House,
    building: Building2,
    package: Package,
    wrench: Wrench,
    mail: Mail,
    'layout-dashboard': LayoutDashboard,
    'user-cog': UserCog,
    inbox: Inbox,
    users: Users,
    shield: Shield,
    'scroll-text': ScrollText,
    settings: Settings,
};

/** Maps the icon names sent by the server navigation to Lucide icons. */
export function NavIcon({ name, className }: { name: string; className?: string }) {
    const Component = icons[name] ?? LayoutDashboard;
    return <Component className={className} aria-hidden />;
}
