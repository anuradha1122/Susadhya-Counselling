import AdminLayout from "@/Layouts/AdminLayout";
import ClientLayout from "@/Layouts/ClientLayout";
import CounsellorLayout from "@/Layouts/CounsellorLayout";
import FinanceLayout from "@/Layouts/FinanceLayout";

function roleNames(user) {
    if (!Array.isArray(user?.roles)) {
        return [];
    }

    return user.roles.map((role) =>
        typeof role === "string"
            ? role
            : role.name,
    );
}

export default function NotificationLayout({
    user,
    title,
    header,
    children,
}) {
    const roles = roleNames(user);

    if (roles.includes("client")) {
        return (
            <ClientLayout
                header={header ?? title}
            >
                {children}
            </ClientLayout>
        );
    }

    if (roles.includes("counsellor")) {
        return (
            <CounsellorLayout
                header={header ?? title}
            >
                {children}
            </CounsellorLayout>
        );
    }

    if (roles.includes("finance_admin")) {
        return (
            <FinanceLayout
                title={title ?? header}
            >
                {children}
            </FinanceLayout>
        );
    }

    return (
        <AdminLayout
            title={title ?? header}
        >
            {children}
        </AdminLayout>
    );
}