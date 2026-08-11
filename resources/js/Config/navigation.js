import {
    CalendarDays,
    CircleDollarSign,
    Clock3,
    FileClock,
    KeyRound,
    LayoutDashboard,
    Settings,
    Stethoscope,
    Users,
} from 'lucide-react';

export const adminNavigation = [
    {
        label: 'Dashboard',
        routeName: 'admin.dashboard',
        icon: LayoutDashboard,
        permission: 'dashboard.admin.view',
    },
    {
        label: 'Counsellors',
        icon: Stethoscope,
        disabled: true,
    },
    {
        label: 'Appointments',
        icon: CalendarDays,
        disabled: true,
    },
    {
        label: 'Payments',
        icon: CircleDollarSign,
        disabled: true,
    },
    {
        label: 'Users',
        routeName: 'admin.users.index',
        icon: Users,
        permission: 'users.view',
    },
    {
        label: 'Roles & Permissions',
        routeName: 'admin.roles.index',
        icon: KeyRound,
        permission: 'roles.view',
    },
    {
        label: 'Audit Logs',
        icon: FileClock,
        disabled: true,
    },
    {
        label: 'Settings',
        icon: Settings,
        disabled: true,
    },
];

export const counsellorNavigation = [
    {
        label: 'Dashboard',
        routeName: 'counsellor.dashboard',
        icon: LayoutDashboard,
        permission: 'dashboard.counsellor.view',
    },
    {
        label: 'My Appointments',
        icon: CalendarDays,
        disabled: true,
    },
    {
        label: 'My Availability',
        icon: Clock3,
        disabled: true,
    },
];
