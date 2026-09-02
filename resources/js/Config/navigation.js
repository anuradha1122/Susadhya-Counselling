import {
    BriefcaseBusiness,
    CalendarDays,
    CircleDollarSign,
    Clock3,
    FileClock,
    HeartHandshake,
    KeyRound,
    LayoutDashboard,
    Settings,
    ShieldCheck,
    Stethoscope,
    Tags,
    UserRoundCog,
    Users,
    Search,
} from "lucide-react";

export const adminNavigation = [
    {
        label: "Dashboard",
        routeName: "admin.dashboard",
        icon: LayoutDashboard,
        permission: "dashboard.admin.view",
    },
    {
        label: "Clients",
        routeName: "admin.clients.index",
        icon: Users,
        permission: "clients.view",
    },
    {
        label: "Counsellors",
        routeName: "admin.counsellors.index",
        icon: Stethoscope,
        permission: "counsellors.view",
    },
    {
        label: "Availability",
        routeName: "admin.availability.index",
        icon: Clock3,
        permission: "dashboard.admin.view",
    },
    {
        label: "Service Categories",
        routeName: "admin.service-categories.index",
        icon: Tags,
        permission: "services.view",
    },
    {
        label: "Counselling Services",
        routeName: "admin.counselling-services.index",
        icon: BriefcaseBusiness,
        permission: "services.view",
    },
    {
        label: "Appointments",
        routeName: "admin.appointments.index",
        icon: CalendarDays,
        permission: "dashboard.admin.view",
    },
    {
        label: "Payments",
        icon: CircleDollarSign,
        disabled: true,
    },
    {
        label: "Users",
        routeName: "admin.users.index",
        icon: Users,
        permission: "users.view",
    },
    {
        label: "Roles & Permissions",
        routeName: "admin.roles.index",
        icon: KeyRound,
        permission: "roles.view",
    },
    {
        label: "Audit Logs",
        icon: FileClock,
        disabled: true,
    },
    {
        label: "Settings",
        icon: Settings,
        disabled: true,
    },
];

export const counsellorNavigation = [
    {
        label: "Dashboard",
        routeName: "counsellor.dashboard",
        icon: LayoutDashboard,
        permission: "dashboard.counsellor.view",
    },
    {
        label: "Appointments",
        routeName: "counsellor.appointments.index",
        icon: CalendarDays,
        permission: "dashboard.counsellor.view",
    },
    {
        label: "My Availability",
        routeName: "counsellor.availability.index",
        icon: Clock3,
        permission: "dashboard.counsellor.view",
    },
];

export const clientNavigation = [
    {
        label: "Dashboard",
        routeName: "client.dashboard",
        icon: LayoutDashboard,
        permission: "dashboard.client.view",
    },
    {
        label: "Find Counsellors",
        routeName: "client.counsellors.index",
        icon: Search,
        permission: "dashboard.client.view",
    },
    {
        label: "My Appointments",
        routeName: "client.appointments.index",
        icon: CalendarDays,
        permission: "dashboard.client.view",
    },
    {
        label: "Client Profile",
        routeName: "client.profile.show",
        icon: UserRoundCog,
        permission: "dashboard.client.view",
    },
    {
        label: "Emergency Contacts",
        routeName: "client.emergency-contacts.index",
        icon: HeartHandshake,
        permission: "dashboard.client.view",
    },
    {
        label: "Counselling Preferences",
        routeName: "client.preferences.show",
        icon: Stethoscope,
        permission: "dashboard.client.view",
    },
    {
        label: "Privacy & Consent",
        routeName: "client.privacy.show",
        icon: ShieldCheck,
        permission: "dashboard.client.view",
    },
    {
        label: "Appointments",
        icon: CalendarDays,
        disabled: true,
    },
    {
        label: "Payments",
        icon: CircleDollarSign,
        disabled: true,
    },
];
