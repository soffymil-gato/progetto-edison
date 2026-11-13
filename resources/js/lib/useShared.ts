import { usePage } from '@inertiajs/react';

import type { CustomerUser, SharedProps, StaffUser } from './types';

export function useShared(): SharedProps {
    return usePage().props as unknown as SharedProps;
}

export function useStaff(): StaffUser | null {
    const { auth, area } = useShared();
    return area === 'admin' ? (auth.user as StaffUser | null) : null;
}

export function useCustomer(): CustomerUser | null {
    const { auth, area } = useShared();
    return area === 'portal' ? (auth.user as CustomerUser | null) : null;
}

/** UI hint only: every action is authorised again on the server. */
export function useCan(permission: string): boolean {
    const staff = useStaff();
    return staff?.permissions.includes(permission) ?? false;
}


/** Pure variant usable inside loops. */
export function routeMatches(current: string | null, pattern: string): boolean {
    if (!current) return false;
    return pattern.endsWith('.*') ? current.startsWith(pattern.slice(0, -1)) : current === pattern;
}
