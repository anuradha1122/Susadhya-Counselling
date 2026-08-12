import ClientLayout from "@/Layouts/ClientLayout";
import { Head } from "@inertiajs/react";
import EmergencyContactForm from "./Partials/EmergencyContactForm";

export default function Edit({ contact }) {
    return (
        <ClientLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Edit Emergency Contact
                    </h2>
                    <p className="mt-1 text-sm text-gray-500">
                        Update emergency contact details and contact permission.
                    </p>
                </div>
            }
        >
            <Head title="Edit Emergency Contact" />

            <div className="py-12">
                <div className="mx-auto max-w-3xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                        <EmergencyContactForm
                            contact={contact}
                            submitRoute={route(
                                "client.emergency-contacts.update",
                                contact.id,
                            )}
                            submitMethod="patch"
                            submitLabel="Update contact"
                        />
                    </div>
                </div>
            </div>
        </ClientLayout>
    );
}
