import ClientLayout from "@/Layouts/ClientLayout";
import { Head } from "@inertiajs/react";
import EmergencyContactForm from "./Partials/EmergencyContactForm";

export default function Create({ contact }) {
    return (
        <ClientLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Add Emergency Contact
                    </h2>
                    <p className="mt-1 text-sm text-gray-500">
                        Add a trusted person who may be contacted during a
                        safety-related emergency.
                    </p>
                </div>
            }
        >
            <Head title="Add Emergency Contact" />

            <div className="py-12">
                <div className="mx-auto max-w-3xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                        <EmergencyContactForm
                            contact={contact}
                            submitRoute={route(
                                "client.emergency-contacts.store",
                            )}
                            submitMethod="post"
                            submitLabel="Save contact"
                        />
                    </div>
                </div>
            </div>
        </ClientLayout>
    );
}
