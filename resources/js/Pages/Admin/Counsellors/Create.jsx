import AdminLayout from "@/Layouts/AdminLayout";
import CounsellorForm from "@/Pages/Admin/Counsellors/Partials/CounsellorForm";
import CounsellorProfilePhotoCard from "@/Pages/Admin/Counsellors/Partials/CounsellorProfilePhotoCard";
import {
    Head,
    useForm,
} from "@inertiajs/react";

export default function Create({
    users,
    specializations,
    languages,
}) {
    const {
        data,
        setData,
        post,
        processing,
        errors,
    } = useForm({
        user_id: "",
        profile_photo: null,
        remove_profile_photo: false,
        registration_number: "",
        professional_title: "",
        nic: "",
        date_of_birth: "",
        gender: "",
        years_of_experience: 0,
        biography: "",
        address: "",
        city: "",
        status: "active",
        specialization_ids: [],
        languages: [],
        qualifications: [],
    });

    const selectedUser =
        users.find(
            (user) =>
                String(
                    user.id,
                )
                === String(
                    data.user_id,
                ),
        );

    const submit = (
        event,
    ) => {
        event.preventDefault();

        post(
            route(
                "admin.counsellors.store",
            ),
            {
                preserveScroll: true,
                forceFormData: true,
            },
        );
    };

    return (
        <AdminLayout title="Create Counsellor">
            <Head title="Create Counsellor" />

            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-bold text-slate-900">
                        Create
                        counsellor
                    </h1>

                    <p className="mt-1 text-sm text-slate-500">
                        Create and
                        associate a
                        counsellor
                        profile with
                        an existing
                        user account.
                    </p>
                </div>

                <CounsellorProfilePhotoCard
                    data={data}
                    setData={
                        setData
                    }
                    errors={
                        errors
                    }
                    currentPhotoUrl={
                        selectedUser?.profile_photo_url
                        ?? null
                    }
                />

                <CounsellorForm
                    data={data}
                    setData={
                        setData
                    }
                    errors={
                        errors
                    }
                    processing={
                        processing
                    }
                    users={
                        users
                    }
                    specializations={
                        specializations
                    }
                    languages={
                        languages
                    }
                    submitLabel="Create counsellor"
                    onSubmit={
                        submit
                    }
                />
            </div>
        </AdminLayout>
    );
}
