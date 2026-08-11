import { counsellorNavigation } from '@/Config/navigation';
import AppLayout from '@/Layouts/AppLayout';

export default function CounsellorLayout({
    title,
    children,
}) {
    return (
        <AppLayout
            title={title}
            navigation={counsellorNavigation}
        >
            {children}
        </AppLayout>
    );
}
