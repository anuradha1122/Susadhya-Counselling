import { adminNavigation } from '@/Config/navigation';
import AppLayout from '@/Layouts/AppLayout';

export default function AdminLayout({
    title,
    children,
}) {
    return (
        <AppLayout
            title={title}
            navigation={adminNavigation}
        >
            {children}
        </AppLayout>
    );
}
