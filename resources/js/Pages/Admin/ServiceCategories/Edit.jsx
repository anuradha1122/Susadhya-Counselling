import AdminLayout from "@/Layouts/AdminLayout";
import { Head, useForm } from "@inertiajs/react";
import CategoryForm from "./CategoryForm";

export default function Edit({ category }) {
    const form = useForm({
        name: category.name ?? "",
        slug: category.slug ?? "",
        description: category.description ?? "",
        display_order: category.display_order ?? 0,
        status: category.status ?? "active",
    });

    form.submit = (event) => {
        event.preventDefault();

        form.put(route("admin.service-categories.update", category.id));
    };

    return (
        <AdminLayout title="Edit Service Category">
            <Head title={`Edit ${category.name}`} />

            <div className="mb-6">
                <h2 className="text-2xl font-bold text-slate-900">
                    Edit service category
                </h2>

                <p className="mt-1 text-sm text-slate-500">
                    Update {category.name}.
                </p>
            </div>

            <CategoryForm form={form} submitLabel="Save changes" />
        </AdminLayout>
    );
}
