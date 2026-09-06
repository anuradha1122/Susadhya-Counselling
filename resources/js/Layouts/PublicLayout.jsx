import PublicFooter from "@/Components/Public/PublicFooter";
import PublicHeader from "@/Components/Public/PublicHeader";

import "@fontsource/playfair-display/600.css";
import "@fontsource/playfair-display/700.css";
import "@fontsource/poppins/400.css";
import "@fontsource/poppins/500.css";
import "@fontsource/poppins/600.css";
import "@fontsource/poppins/700.css";


export default function PublicLayout({
    children,
}) {
    return (
        <div className="susadhya-public">
            <PublicHeader />

            <main>
                {children}
            </main>

            <PublicFooter />
        </div>
    );
}
