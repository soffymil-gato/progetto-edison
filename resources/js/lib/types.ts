export type AreaName = 'public' | 'portal' | 'admin';

export type NavItem = { label: string; href: string; icon: string; match: string };

export type CompanySettings = {
    legal_name: string | null;
    short_description: string | null;
    registered_office: string | null;
    vat_number: string | null;
    tax_code: string | null;
    rea: string | null;
    share_capital: string | null;
    phone: string | null;
    email: string | null;
    pec: string | null;
    office_hours: string | null;
};

export type PickupSettings = { address: string | null; hours: string | null; notes: string | null };

export type StaffUser = {
    name: string;
    email: string;
    roles: string[];
    isSuperAdmin: boolean;
    permissions: string[];
};

export type CustomerUser = { name: string; email: string; username: string | null };

export type SharedProps = {
    area: AreaName;
    route: string | null;
    appName: string;
    xsrfCookie: string;
    brand: { logoUrl: string | null };
    company: CompanySettings;
    auth: { user: StaffUser | CustomerUser | null };
    nav: NavItem[];
    urls: Record<string, string>;
    flash: { success: string | null; error: string | null };
    errors: Record<string, string>;
};

export type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: { url: string | null; label: string; active: boolean }[];
    prev_page_url: string | null;
    next_page_url: string | null;
};

export type Option = { value: string; label: string };

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: SharedProps;
    }
}
