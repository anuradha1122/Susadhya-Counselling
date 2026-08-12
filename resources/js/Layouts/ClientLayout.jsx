import { clientNavigation } from "@/Config/navigation";
import AppLayout from "@/Layouts/AppLayout";

export default function ClientLayout({ header, children }) {
    return (
        <AppLayout navigation={clientNavigation} header={header}>
            {children}
        </AppLayout>
    );
}
