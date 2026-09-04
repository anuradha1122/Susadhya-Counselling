import NotificationBell from "@/Components/Notifications/NotificationBell";
import { counsellorNavigation } from "@/Config/navigation";
import AppLayout from "@/Layouts/AppLayout";

export default function CounsellorLayout({
    header,
    children,
}) {
    return (
        <AppLayout
            navigation={counsellorNavigation}
            header={header}
        >
            <div className="mb-4 flex justify-end">
                <NotificationBell />
            </div>

            {children}
        </AppLayout>
    );
}