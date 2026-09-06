import Checkbox from "@/Components/Checkbox";
import InputError from "@/Components/InputError";
import PublicAuthLayout from "@/Layouts/PublicAuthLayout";
import {
    Head,
    Link,
    useForm,
} from "@inertiajs/react";
import {
    ArrowRight,
    LockKeyhole,
    Mail,
    ShieldCheck,
} from "lucide-react";

export default function Login({
    status,
    canResetPassword,
}) {
    const {
        data,
        setData,
        post,
        processing,
        errors,
        reset,
    } = useForm({
        email: "",
        password: "",
        remember: false,
    });

    const submit = (event) => {
        event.preventDefault();

        post(route("login"), {
            onFinish: () =>
                reset("password"),
        });
    };

    return (
        <PublicAuthLayout
                title="Welcome back"
                description="Sign in securely to manage appointments and access your Susadhya account."
                image="/images/site-defaults/auth-login.jpg"
                imageAlt="Professional counselling session"
            >
            <Head title="Log in" />

            {status && (
                <div className="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium leading-6 text-emerald-700">
                    {status}
                </div>
            )}

            <form
                onSubmit={submit}
                className="space-y-6"
            >
                {/* Email */}
                <div>
                    <label
                        htmlFor="email"
                        className="text-sm font-semibold text-[#123B5B]"
                    >
                        Email address
                    </label>

                    <div className="relative mt-2">
                        <Mail className="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />

                        <input
                            id="email"
                            type="email"
                            name="email"
                            value={data.email}
                            autoComplete="username"
                            autoFocus
                            onChange={(event) =>
                                setData(
                                    "email",
                                    event.target
                                        .value,
                                )
                            }
                            placeholder="you@example.com"
                            className="block h-12 w-full rounded-xl border border-slate-300 bg-white pl-11 pr-4 text-sm text-slate-700 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-[#2A9D8F] focus:ring-2 focus:ring-[#2A9D8F]/15"
                        />
                    </div>

                    <InputError
                        message={errors.email}
                        className="mt-2"
                    />
                </div>

                {/* Password */}
                <div>
                    <div className="flex items-center justify-between gap-4">
                        <label
                            htmlFor="password"
                            className="text-sm font-semibold text-[#123B5B]"
                        >
                            Password
                        </label>

                        {canResetPassword && (
                            <Link
                                href={route(
                                    "password.request",
                                )}
                                className="text-xs font-semibold text-[#168C9D] transition hover:text-[#123B5B]"
                            >
                                Forgot password?
                            </Link>
                        )}
                    </div>

                    <div className="relative mt-2">
                        <LockKeyhole className="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />

                        <input
                            id="password"
                            type="password"
                            name="password"
                            value={data.password}
                            autoComplete="current-password"
                            onChange={(event) =>
                                setData(
                                    "password",
                                    event.target
                                        .value,
                                )
                            }
                            placeholder="Enter your password"
                            className="block h-12 w-full rounded-xl border border-slate-300 bg-white pl-11 pr-4 text-sm text-slate-700 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-[#2A9D8F] focus:ring-2 focus:ring-[#2A9D8F]/15"
                        />
                    </div>

                    <InputError
                        message={
                            errors.password
                        }
                        className="mt-2"
                    />
                </div>

                {/* Remember */}
                <div className="flex items-center justify-between gap-4">
                    <label className="flex cursor-pointer items-center gap-3">
                        <Checkbox
                            name="remember"
                            checked={
                                data.remember
                            }
                            onChange={(
                                event,
                            ) =>
                                setData(
                                    "remember",
                                    event.target
                                        .checked,
                                )
                            }
                        />

                        <span className="text-sm text-slate-600">
                            Remember me
                        </span>
                    </label>

                    <div className="hidden items-center gap-2 text-xs text-slate-400 sm:flex">
                        <ShieldCheck className="h-4 w-4 text-[#7FB069]" />
                        Secure sign in
                    </div>
                </div>

                {/* Submit */}
                <button
                    type="submit"
                    disabled={processing}
                    className="inline-flex h-12 w-full items-center justify-center gap-2 rounded-full bg-[#168C9D] px-6 text-sm font-semibold text-white shadow-md shadow-cyan-900/10 transition hover:-translate-y-0.5 hover:bg-[#147D8B] hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {processing
                        ? "Signing in..."
                        : "Sign in"}

                    {!processing && (
                        <ArrowRight className="h-4 w-4" />
                    )}
                </button>

                {/* Register */}
                <div className="border-t border-slate-200 pt-6 text-center">
                    <p className="text-sm text-slate-500">
                        New to Susadhya?{" "}
                        <Link
                            href={route(
                                "register",
                            )}
                            className="font-semibold text-[#168C9D] transition hover:text-[#123B5B]"
                        >
                            Create a client
                            account
                        </Link>
                    </p>
                </div>
            </form>
        </PublicAuthLayout>
    );
}
