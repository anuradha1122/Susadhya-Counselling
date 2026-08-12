import DangerButton from "@/Components/DangerButton";
import Pagination from "@/Components/Pagination";
import PrimaryButton from "@/Components/PrimaryButton";
import SecondaryButton from "@/Components/SecondaryButton";
import TextInput from "@/Components/TextInput";
import AdminLayout from "@/Layouts/AdminLayout";
import { Head, Link, router, useForm } from "@inertiajs/react";

function formatValue(value) {
    if (value === null || value === undefined || value === "") {
        return "Not provided";
    }

    return String(value)
        .replaceAll("_", " ")
        .replace(/\b\w/g, (character) => character.toUpperCase());
}

function StatusBadge({ status }) {
    const classes = {
        active: "bg-green-100 text-green-800",
        inactive: "bg-amber-100 text-amber-800",
        archived: "bg-gray-100 text-gray-800",
    };

    return (
        <span
            className={`rounded-full px-3 py-1 text-xs font-semibold ${
                classes[status] ?? "bg-gray-100 text-gray-800"
            }`}
        >
            {formatValue(status)}
        </span>
    );
}

function CompletionBadge({ complete }) {
    return (
        <span
            className={`rounded-full px-3 py-1 text-xs font-semibold ${
                complete
                    ? "bg-indigo-100 text-indigo-800"
                    : "bg-red-100 text-red-800"
            }`}
        >
            {complete ? "Complete" : "Incomplete"}
        </span>
    );
}

export default function Index({
    clients,
    filters,
    statusOptions,
    completionOptions,
}) {
    const { data, setData, get, processing } = useForm({
        search: filters.search ?? "",
        status: filters.status ?? "",
        completion: filters.completion ?? "",
    });

    const submit = (event) => {
        event.preventDefault();

        get(route("admin.clients.index"), {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        });
    };

    const resetFilters = () => {
        router.get(
            route("admin.clients.index"),
            {},
            {
                preserveScroll: true,
                replace: true,
            },
        );
    };

    const changeStatus = (client, status) => {
        router.patch(
            route("admin.clients.status", client.id),
            {
                status,
            },
            {
                preserveScroll: true,
            },
        );
    };

    const archiveClient = (client) => {
        const confirmed = window.confirm(
            `Archive client "${client.full_name}"? Their user account will be deactivated.`,
        );

        if (!confirmed) {
            return;
        }

        router.delete(route("admin.clients.destroy", client.id), {
            preserveScroll: true,
        });
    };

    const restoreClient = (client) => {
        router.patch(
            route("admin.clients.restore", client.id),
            {},
            {
                preserveScroll: true,
            },
        );
    };

    return (
        <AdminLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Clients
                    </h2>
                    <p className="mt-1 text-sm text-gray-500">
                        Search, review, activate, deactivate, archive, and
                        restore client profiles.
                    </p>
                </div>
            }
        >
            <Head title="Clients" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <form
                        onSubmit={submit}
                        className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg"
                    >
                        <div className="grid gap-4 lg:grid-cols-4">
                            <div className="lg:col-span-2">
                                <label
                                    htmlFor="search"
                                    className="text-sm font-medium text-gray-700"
                                >
                                    Search
                                </label>

                                <TextInput
                                    id="search"
                                    value={data.search}
                                    className="mt-1 block w-full"
                                    placeholder="Name, email, phone, city, district"
                                    onChange={(event) =>
                                        setData("search", event.target.value)
                                    }
                                />
                            </div>

                            <div>
                                <label
                                    htmlFor="status"
                                    className="text-sm font-medium text-gray-700"
                                >
                                    Status
                                </label>

                                <select
                                    id="status"
                                    value={data.status}
                                    onChange={(event) =>
                                        setData("status", event.target.value)
                                    }
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    {statusOptions.map((option) => (
                                        <option
                                            key={option.value}
                                            value={option.value}
                                        >
                                            {option.label}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div>
                                <label
                                    htmlFor="completion"
                                    className="text-sm font-medium text-gray-700"
                                >
                                    Completion
                                </label>

                                <select
                                    id="completion"
                                    value={data.completion}
                                    onChange={(event) =>
                                        setData(
                                            "completion",
                                            event.target.value,
                                        )
                                    }
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    {completionOptions.map((option) => (
                                        <option
                                            key={option.value}
                                            value={option.value}
                                        >
                                            {option.label}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        </div>

                        <div className="mt-4 flex flex-wrap justify-end gap-3">
                            <SecondaryButton
                                type="button"
                                onClick={resetFilters}
                            >
                                Reset
                            </SecondaryButton>

                            <PrimaryButton disabled={processing}>
                                Apply filters
                            </PrimaryButton>
                        </div>
                    </form>

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Client
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Contact
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Location
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Status
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Profile
                                        </th>
                                        <th className="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>

                                <tbody className="divide-y divide-gray-200 bg-white">
                                    {clients.data.length === 0 ? (
                                        <tr>
                                            <td
                                                colSpan="6"
                                                className="px-6 py-8 text-center text-sm text-gray-500"
                                            >
                                                No clients found.
                                            </td>
                                        </tr>
                                    ) : (
                                        clients.data.map((client) => (
                                            <tr key={client.id}>
                                                <td className="px-6 py-4">
                                                    <div className="text-sm font-semibold text-gray-900">
                                                        {client.full_name}
                                                    </div>

                                                    <div className="mt-1 text-sm text-gray-500">
                                                        Preferred:{" "}
                                                        {formatValue(
                                                            client.preferred_name,
                                                        )}
                                                    </div>

                                                    <div className="mt-1 text-xs text-gray-500">
                                                        Registered:{" "}
                                                        {formatValue(
                                                            client.created_at,
                                                        )}
                                                    </div>
                                                </td>

                                                <td className="px-6 py-4 text-sm text-gray-600">
                                                    <div>
                                                        {formatValue(
                                                            client.email,
                                                        )}
                                                    </div>
                                                    <div className="mt-1">
                                                        {formatValue(
                                                            client.phone,
                                                        )}
                                                    </div>
                                                    <div className="mt-1 text-xs text-gray-500">
                                                        Contact:{" "}
                                                        {formatValue(
                                                            client.preferred_contact_method,
                                                        )}
                                                    </div>
                                                </td>

                                                <td className="px-6 py-4 text-sm text-gray-600">
                                                    <div>
                                                        {formatValue(
                                                            client.city,
                                                        )}
                                                    </div>
                                                    <div className="mt-1 text-xs text-gray-500">
                                                        {formatValue(
                                                            client.district,
                                                        )}
                                                    </div>
                                                    <div className="mt-1 text-xs text-gray-500">
                                                        Language:{" "}
                                                        {formatValue(
                                                            client.preferred_language,
                                                        )}
                                                    </div>
                                                </td>

                                                <td className="px-6 py-4">
                                                    <StatusBadge
                                                        status={client.status}
                                                    />

                                                    <div className="mt-2 text-xs text-gray-500">
                                                        User account:{" "}
                                                        {client.is_user_active
                                                            ? "Active"
                                                            : "Inactive"}
                                                    </div>
                                                </td>

                                                <td className="px-6 py-4">
                                                    <CompletionBadge
                                                        complete={
                                                            client.is_complete
                                                        }
                                                    />

                                                    <div className="mt-2 text-xs text-gray-500">
                                                        Missing:{" "}
                                                        {
                                                            client.missing_fields_count
                                                        }
                                                    </div>

                                                    <div className="mt-1 text-xs text-gray-500">
                                                        Emergency contacts:{" "}
                                                        {
                                                            client.emergency_contacts_count
                                                        }
                                                    </div>
                                                </td>

                                                <td className="px-6 py-4 text-right">
                                                    <div className="flex flex-wrap justify-end gap-2">
                                                        <Link
                                                            href={route(
                                                                "admin.clients.show",
                                                                client.id,
                                                            )}
                                                        >
                                                            <SecondaryButton>
                                                                View
                                                            </SecondaryButton>
                                                        </Link>

                                                        {client.status ===
                                                            "active" && (
                                                            <SecondaryButton
                                                                type="button"
                                                                onClick={() =>
                                                                    changeStatus(
                                                                        client,
                                                                        "inactive",
                                                                    )
                                                                }
                                                            >
                                                                Deactivate
                                                            </SecondaryButton>
                                                        )}

                                                        {client.status ===
                                                            "inactive" && (
                                                            <SecondaryButton
                                                                type="button"
                                                                onClick={() =>
                                                                    changeStatus(
                                                                        client,
                                                                        "active",
                                                                    )
                                                                }
                                                            >
                                                                Activate
                                                            </SecondaryButton>
                                                        )}

                                                        {client.status !==
                                                            "archived" && (
                                                            <DangerButton
                                                                type="button"
                                                                onClick={() =>
                                                                    archiveClient(
                                                                        client,
                                                                    )
                                                                }
                                                            >
                                                                Archive
                                                            </DangerButton>
                                                        )}

                                                        {client.status ===
                                                            "archived" && (
                                                            <PrimaryButton
                                                                type="button"
                                                                onClick={() =>
                                                                    restoreClient(
                                                                        client,
                                                                    )
                                                                }
                                                            >
                                                                Restore
                                                            </PrimaryButton>
                                                        )}
                                                    </div>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>

                        <Pagination links={clients.links} />
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
