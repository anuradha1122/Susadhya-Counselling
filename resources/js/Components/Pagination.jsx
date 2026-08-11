import { Link } from '@inertiajs/react';

export default function Pagination({ links }) {
    if (!links || links.length <= 3) return null;

    return (
        <nav className="mt-6 flex flex-wrap gap-2" aria-label="Pagination">
            {links.map((link, index) => link.url ? (
                <Link
                    key={index}
                    href={link.url}
                    preserveScroll
                    className={`rounded-lg border px-3 py-2 text-sm ${link.active ? 'border-teal-600 bg-teal-600 text-white' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'}`}
                    dangerouslySetInnerHTML={{ __html: link.label }}
                />
            ) : (
                <span key={index} className="rounded-lg border border-slate-100 px-3 py-2 text-sm text-slate-300" dangerouslySetInnerHTML={{ __html: link.label }} />
            ))}
        </nav>
    );
}
