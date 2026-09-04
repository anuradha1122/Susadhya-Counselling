import {
    BriefcaseBusiness,
    BriefcaseMedical,
    CalendarDays,
    CircleDollarSign,
    ClipboardList,
    ClipboardPenLine,
    Clock3,
    FileClock,
    FileText,
    HeartHandshake,
    KeyRound,
    LayoutDashboard,
    Search,
    Settings,
    ShieldCheck,
    Stethoscope,
    Tags,
    UserRoundCog,
    Users,
    ReceiptText,
    RotateCcw,
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
        label: "Client Intakes",
        routeName: "admin.intakes.index",
        icon: ClipboardList,
        permission: "dashboard.admin.view",
    },
    {
        label: "Sessions",
        routeName: "admin.sessions.index",
        icon: ClipboardPenLine,
        permission: "dashboard.admin.view",
    },

    // M12 — Case & Clinical Records
    {
        label: "Clinical Supervision",
        routeName: "clinical-supervisor.cases.index",
        icon: BriefcaseMedical,
        permission: "clinical.records.review",
    },

    // M13 — Documents & Secure Files
    {
        label: "Clinical Documents",
        routeName: "clinical-supervisor.documents.index",
        icon: FileText,
        permission: "documents.case.review",
    },
    {
        label: "Documents",
        routeName: "admin.documents.index",
        icon: FileText,
        permission: "documents.admin.manage",
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
    {
        label: "Client Intakes",
        routeName: "counsellor.intakes.index",
        icon: ClipboardList,
        permission: "dashboard.counsellor.view",
    },
    {
        label: "Sessions",
        routeName: "counsellor.sessions.index",
        icon: ClipboardPenLine,
        permission: "dashboard.counsellor.view",
    },

    // M12 — Case & Clinical Records
    {
        label: "Cases",
        routeName: "counsellor.cases.index",
        icon: BriefcaseMedical,
        permission: "clinical.records.manage",
    },

    // M13 — Documents & Secure Files
    {
        label: "Documents",
        routeName: "counsellor.documents.index",
        icon: FileText,
        permission: "documents.case.manage",
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
        label: "Intake Form",
        routeName: "client.intake.edit",
        icon: ClipboardList,
        permission: "dashboard.client.view",
    },
    {
        label: "My Sessions",
        routeName: "client.sessions.index",
        icon: ClipboardPenLine,
        permission: "dashboard.client.view",
    },

    // M13 — Documents & Secure Files
    {
        label: "My Documents",
        routeName: "client.documents.index",
        icon: FileText,
        permission: "documents.client.manage",
    },
    {
        label: "My Payments",
        routeName: "client.payments.index",
        icon: ReceiptText,
        permission: "payments.client.manage",
    },
];

export const financeNavigation = [
    {
        label: "Payments",
        routeName: "finance.payments.index",
        icon: CircleDollarSign,
        permission: "payments.finance.view",
    },
    {
        label: "Refunds",
        routeName: "finance.refunds.index",
        icon: RotateCcw,
        permission: "payments.refunds.manage",
    },
];
