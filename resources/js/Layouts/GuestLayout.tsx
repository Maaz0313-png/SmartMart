import React, { ReactNode } from "react";

interface GuestLayoutProps {
    children: ReactNode;
}

export default function GuestLayout({ children }: GuestLayoutProps) {
    return (
        <div className="min-h-screen">
            <nav className="bg-white shadow-sm border-b">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between h-16">
                        <div className="flex items-center">
                            <a
                                href="/"
                                className="text-2xl font-bold text-indigo-600"
                            >
                                SmartMart
                            </a>
                        </div>

                        <div className="flex items-center space-x-4">
                            <a
                                href="/products"
                                className="text-gray-700 hover:text-indigo-600 transition"
                            >
                                Products
                            </a>
                            <a
                                href="/subscriptions"
                                className="text-gray-700 hover:text-indigo-600 transition"
                            >
                                Subscriptions
                            </a>
                            <a
                                href="/login"
                                className="text-gray-700 hover:text-indigo-600 transition"
                            >
                                Login
                            </a>
                            <a
                                href="/register"
                                className="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition"
                            >
                                Sign Up
                            </a>
                        </div>
                    </div>
                </div>
            </nav>

            <main>{children}</main>

            <footer className="bg-gray-800 text-white py-12">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <div>
                            <h3 className="text-lg font-semibold mb-4">
                                SmartMart
                            </h3>
                            <p className="text-gray-300">
                                Your AI-driven marketplace for smart shopping.
                            </p>
                        </div>
                        <div>
                            <h4 className="font-semibold mb-4">Shop</h4>
                            <ul className="space-y-2 text-gray-300">
                                <li>
                                    <a
                                        href="/products"
                                        className="hover:text-white transition"
                                    >
                                        Products
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="/subscriptions"
                                        className="hover:text-white transition"
                                    >
                                        Subscriptions
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="/deals"
                                        className="hover:text-white transition"
                                    >
                                        Deals
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div>
                            <h4 className="font-semibold mb-4">Support</h4>
                            <ul className="space-y-2 text-gray-300">
                                <li>
                                    <a
                                        href="/help"
                                        className="hover:text-white transition"
                                    >
                                        Help Center
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="/contact"
                                        className="hover:text-white transition"
                                    >
                                        Contact Us
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="/returns"
                                        className="hover:text-white transition"
                                    >
                                        Returns
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div>
                            <h4 className="font-semibold mb-4">Company</h4>
                            <ul className="space-y-2 text-gray-300">
                                <li>
                                    <a
                                        href="/about"
                                        className="hover:text-white transition"
                                    >
                                        About
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="/careers"
                                        className="hover:text-white transition"
                                    >
                                        Careers
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="/privacy"
                                        className="hover:text-white transition"
                                    >
                                        Privacy
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div className="mt-8 pt-8 border-t border-gray-700 text-center text-gray-300">
                        <p>&copy; 2024 SmartMart. All rights reserved.</p>
                    </div>
                </div>
            </footer>
        </div>
    );
}
