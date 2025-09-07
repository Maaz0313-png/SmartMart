import React from "react";
import { Head, Link, Form } from "@inertiajs/react";
import GuestLayout from "@/Layouts/GuestLayout";

interface RegisterProps {
    // Add any props if needed
}

export default function Register() {

    return (
        <GuestLayout>
            <Head title="Register" />

            <div className="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
                <div className="max-w-md w-full space-y-8">
                    <div>
                        <h2 className="mt-6 text-center text-3xl font-extrabold text-gray-900">
                            Create your account
                        </h2>
                        <p className="mt-2 text-center text-sm text-gray-600">
                            Or{" "}
                            <Link
                                href={route("login")}
                                className="font-medium text-blue-600 hover:text-blue-500"
                            >
                                sign in to your existing account
                            </Link>
                        </p>
                    </div>

                    <Form action={route("register")} method="post" className="mt-8 space-y-6">
                        {({ errors, processing }) => (
                            <>
                        <div className="rounded-md shadow-sm -space-y-px">
                            <div>
                                <label htmlFor="name" className="sr-only">
                                    Name
                                </label>
                                <input
                                    id="name"
                                    name="name"
                                    type="text"
                                    required
                                    className="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                                    placeholder="Full name"
                                />
                                {errors.name && (
                                    <div className="text-red-600 text-sm">
                                        {errors.name}
                                    </div>
                                )}
                            </div>

                            <div>
                                <label htmlFor="email" className="sr-only">
                                    Email address
                                </label>
                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    required
                                    className="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                                    placeholder="Email address"
                                />
                                {errors.email && (
                                    <div className="text-red-600 text-sm">
                                        {errors.email}
                                    </div>
                                )}
                            </div>

                            <div>
                                <label htmlFor="phone" className="sr-only">
                                    Phone
                                </label>
                                <input
                                    id="phone"
                                    name="phone"
                                    type="tel"
                                    className="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                                    placeholder="Phone number (optional)"
                                />
                                {errors.phone && (
                                    <div className="text-red-600 text-sm">
                                        {errors.phone}
                                    </div>
                                )}
                            </div>

                            <div>
                                <label htmlFor="user_type" className="sr-only">
                                    Account Type
                                </label>
                                <select
                                    id="user_type"
                                    name="user_type"
                                    className="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                                    defaultValue="buyer"
                                >
                                    <option value="buyer">Buyer</option>
                                    <option value="seller">Seller</option>
                                </select>
                                {errors.user_type && (
                                    <div className="text-red-600 text-sm">
                                        {errors.user_type}
                                    </div>
                                )}
                            </div>

                            <div>
                                <label htmlFor="password" className="sr-only">
                                    Password
                                </label>
                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    required
                                    className="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                                    placeholder="Password"
                                />
                                {errors.password && (
                                    <div className="text-red-600 text-sm">
                                        {errors.password}
                                    </div>
                                )}
                            </div>

                            <div>
                                <label
                                    htmlFor="password_confirmation"
                                    className="sr-only"
                                >
                                    Confirm Password
                                </label>
                                <input
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    type="password"
                                    required
                                    className="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                                    placeholder="Confirm password"
                                />
                                {errors.password_confirmation && (
                                    <div className="text-red-600 text-sm">
                                        {errors.password_confirmation}
                                    </div>
                                )}
                            </div>
                        </div>

                        <div>
                            <button
                                type="submit"
                                disabled={processing}
                                className="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                            >
                                {processing
                                    ? "Creating account..."
                                    : "Create account"}
                            </button>
                        </div>
                            </>
                        )}
                    </Form>
                </div>
            </div>
        </GuestLayout>
    );
}
