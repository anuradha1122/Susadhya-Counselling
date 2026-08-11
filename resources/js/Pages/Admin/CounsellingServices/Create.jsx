import AdminLayout from "@/Layouts/AdminLayout";
import { Head, useForm } from "@inertiajs/react";
import ServiceForm from "./ServiceForm";

export default function Create({
    categories,
    serviceModes,
    targetAgeGroups,
    durations,
    selectedCategoryId,
}) {
    const form = useForm({
        service_category_id: selectedCategoryId ?? "",
        name: "",
        slug: "",
        short_description: "",
        description: "",
        duration_minutes: 60,
        service_mode: "both",
        target_age_group: "all_ages",
        minimum_age: "",
        maximum_age: "",
        price: "",
        currency: "LKR",
        display_order: 0,
        status: "active",
    });

    form.submit = (event) => {
        event.preventDefault();

        form.post(route("admin.counselling-services.store"));
    };

    return (
        <AdminLayout title="Create Counselling Service">
            <Head title="Create Counselling Service" />

            <div className="mb-6">
                <h2 className="text-2xl font-bold text-slate-900">
                    Create counselling service
                </h2>

                <p className="mt-1 text-sm text-slate-500">
                    Define the session, audience, delivery mode and price.
                </p>
            </div>

            <ServiceForm
                form={form}
                categories={categories}
                serviceModes={serviceModes}
                targetAgeGroups={targetAgeGroups}
                durations={durations}
                submitLabel="Create service"
            />
        </AdminLayout>
    );
}
