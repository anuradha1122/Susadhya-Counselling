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
            {children}
        </AppLayout>
    );
}
