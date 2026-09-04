import NotificationBell from "@/Components/Notifications/NotificationBell";
import { clientNavigation } from "@/Config/navigation";
import AppLayout from "@/Layouts/AppLayout";

export default function ClientLayout({
    header,
    children,
}) {
    return (
        <AppLayout
            navigation={clientNavigation}
            header={header}
        >
            <div className="mb-4 flex justify-end">
                <NotificationBell />
            </div>

            {children}
        </AppLayout>
    );
}