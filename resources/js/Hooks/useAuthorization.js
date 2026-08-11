import { usePage } from '@inertiajs/react';

export default function useAuthorization() {
    const { auth } = usePage().props;

    const user = auth?.user ?? null;
    const roles = auth?.roles ?? [];
    const permissions = auth?.permissions ?? [];

    const hasRole = (role) => {
        return roles.includes(role);
    };

    const hasAnyRole = (requiredRoles) => {
        return requiredRoles.some((role) => {
            return roles.includes(role);
        });
    };

    const can = (permission) => {
        return (
            hasRole('super_admin') ||
            permissions.includes(permission)
        );
    };

    return {
        user,
        roles,
        permissions,
        hasRole,
        hasAnyRole,
        can,
    };
}
