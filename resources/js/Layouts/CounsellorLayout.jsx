import { counsellorNavigation } from "@/Config/navigation";
import AppLayout from "@/Layouts/AppLayout";

export default function CounsellorLayout({ header, children }) {
    return (
        <AppLayout navigation={counsellorNavigation} header={header}>
            {children}
        </AppLayout>
    );
}
