import {
    Siren,
    ClipboardCheck,
    ArchiveRestore,
    AlertTriangle,
    BriefcaseBusiness,
    BriefcaseMedical,
    BarChart3,
    CalendarDays,
    CircleDollarSign,
    ClipboardList,
    ClipboardPenLine,
    Clock3,
    FileClock,
    FilePenLine,
    FileText,
    HeartHandshake,
    KeyRound,
    LayoutDashboard,
    ReceiptText,
    RotateCcw,
    Search,
    Settings,
    ShieldAlert,
    ShieldCheck,
    Stethoscope,
    Tags,
    UserRoundCog,
    Users,
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

    // M16 — Admin Operations
    {
        label: "Operations",
        routeName: "admin.operations.index",
        icon: AlertTriangle,
        permission: "admin.operations.view",
    },
    {
        label: "Case Escalations",
        routeName: "admin.case-escalations.index",
        icon: ShieldAlert,
        permission: "admin.case-escalations.manage",
    },
    {
        label: "Reports",
        routeName: "admin.reports.index",
        icon: BarChart3,
        permission: "reports.operational.view",
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

    // Finance remains isolated from ordinary Admin.
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

    // M16 — Operational Content
    {
        label: "Content Snippets",
        routeName: "admin.content-snippets.index",
        icon: FilePenLine,
        permission: "admin.content.manage",
    },

    {
        label: "Audit Logs",
        routeName: "compliance.audit-events.index",
        icon: FileClock,
        permission: "compliance.audit.view",
    },

    {
        label: "Privacy & Compliance",
        routeName: "compliance.dashboard",
        icon: ShieldCheck,
        permission: "compliance.audit.view",
    },

    // M16 — Master Settings
    {
        label: "Settings",
        routeName: "admin.settings.index",
        icon: Settings,
        permission: "settings.view",
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

    // M14 — Payments
    {
        label: "My Payments",
        routeName: "client.payments.index",
        icon: ReceiptText,
        permission: "payments.client.manage",
    },
    {
        label: "Privacy Requests",
        routeName: "client.privacy-requests.index",
        icon: ShieldCheck,
        permission: "privacy.requests.submit",
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
    {
        label: "Reports",
        routeName: "finance.reports.index",
        icon: BarChart3,
        permission: "reports.finance.view",
    },
];

export const complianceNavigation = [
    {
        label: "Dashboard",
        routeName: "compliance.dashboard",
        icon: LayoutDashboard,
        permission: "compliance.audit.view",
    },
    {
        label: "Audit Logs",
        routeName: "compliance.audit-events.index",
        icon: FileClock,
        permission: "compliance.audit.view",
    },
    {
        label: "Privacy Requests",
        routeName: "compliance.privacy-requests.index",
        icon: ShieldCheck,
        permission: "compliance.privacy.view",
    },
    {
        label: "Consent History",
        routeName: "compliance.consents.index",
        icon: ClipboardCheck,
        permission: "compliance.consents.view",
    },
    {
        label: "Retention",
        routeName: "compliance.retention.index",
        icon: ArchiveRestore,
        permission: "compliance.retention.view",
    },
    {
        label: "Breach Register",
        routeName: "compliance.breaches.index",
        icon: Siren,
        permission: "compliance.breaches.view",
    },
];
