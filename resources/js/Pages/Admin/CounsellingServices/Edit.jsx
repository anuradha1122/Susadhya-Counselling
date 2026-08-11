import AdminLayout from "@/Layouts/AdminLayout";
import { Head, useForm } from "@inertiajs/react";
import ServiceForm from "./ServiceForm";

export default function Edit({
    service,
    categories,
    serviceModes,
    targetAgeGroups,
    durations,
}) {
    const form = useForm({
        service_category_id: service.service_category_id ?? "",
        name: service.name ?? "",
        slug: service.slug ?? "",
        short_description: service.short_description ?? "",
        description: service.description ?? "",
        duration_minutes: service.duration_minutes ?? 60,
        service_mode: service.service_mode ?? "both",
        target_age_group: service.target_age_group ?? "all_ages",
        minimum_age: service.minimum_age ?? "",
        maximum_age: service.maximum_age ?? "",
        price: service.price ?? "",
        currency: service.currency ?? "LKR",
        display_order: service.display_order ?? 0,
        status: service.status ?? "active",
    });

    form.submit = (event) => {
        event.preventDefault();

        form.put(route("admin.counselling-services.update", service.id));
    };

    return (
        <AdminLayout title="Edit Counselling Service">
            <Head title={`Edit ${service.name}`} />

            <div className="mb-6">
                <h2 className="text-2xl font-bold text-slate-900">
                    Edit counselling service
                </h2>

                <p className="mt-1 text-sm text-slate-500">
                    Update {service.name}.
                </p>
            </div>

            <ServiceForm
                form={form}
                categories={categories}
                serviceModes={serviceModes}
                targetAgeGroups={targetAgeGroups}
                durations={durations}
                submitLabel="Save changes"
            />
        </AdminLayout>
    );
}
