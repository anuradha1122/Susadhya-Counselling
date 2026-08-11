import {
    BriefcaseBusiness,
    CalendarDays,
    CircleDollarSign,
    Clock3,
    FileClock,
    KeyRound,
    LayoutDashboard,
    Settings,
    Stethoscope,
    Users,
    Tags,
} from "lucide-react";

export const adminNavigation = [
    {
        label: "Dashboard",
        routeName: "admin.dashboard",
        icon: LayoutDashboard,
        permission: "dashboard.admin.view",
    },
    {
        label: "Counsellors",
        routeName: "admin.counsellors.index",
        icon: Stethoscope,
        permission: "counsellors.view",
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
        icon: CalendarDays,
        disabled: true,
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
        label: "My Appointments",
        icon: CalendarDays,
        disabled: true,
    },
    {
        label: "My Availability",
        icon: Clock3,
        disabled: true,
    },
];
