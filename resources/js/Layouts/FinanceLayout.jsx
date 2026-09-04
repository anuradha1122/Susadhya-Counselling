import NotificationBell from "@/Components/Notifications/NotificationBell";
import { financeNavigation } from "@/Config/navigation";
import AppLayout from "@/Layouts/AppLayout";

export default function FinanceLayout({
    title,
    children,
}) {
    return (
        <AppLayout
            title={title}
            navigation={financeNavigation}
        >
            <div className="mb-4 flex justify-end">
                <NotificationBell />
            </div>

            {children}
        </AppLayout>
    );
}