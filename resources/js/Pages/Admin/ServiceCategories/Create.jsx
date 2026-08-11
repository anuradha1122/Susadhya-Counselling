import AdminLayout from "@/Layouts/AdminLayout";
import { Head, useForm } from "@inertiajs/react";
import CategoryForm from "./CategoryForm";

export default function Create() {
    const form = useForm({
        name: "",
        slug: "",
        description: "",
        display_order: 0,
        status: "active",
    });

    form.submit = (event) => {
        event.preventDefault();

        form.post(route("admin.service-categories.store"));
    };

    return (
        <AdminLayout title="Create Service Category">
            <Head title="Create Service Category" />

            <div className="mb-6">
                <h2 className="text-2xl font-bold text-slate-900">
                    Create service category
                </h2>

                <p className="mt-1 text-sm text-slate-500">
                    Group related counselling services into a reusable category.
                </p>
            </div>

            <CategoryForm form={form} submitLabel="Create category" />
        </AdminLayout>
    );
}
