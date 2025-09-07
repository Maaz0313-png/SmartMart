import React, { useState } from 'react';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Form } from '@inertiajs/react';
import PrimaryButton from '@/Components/PrimaryButton';

export default function PrivacyPolicy() {
    const [consentData, setConsentData] = useState({
        marketing: false,
        analytics: false,
        cookies: true, // Essential cookies always required
        essential: true
    });

    const handleConsentChange = (type: string, value: boolean) => {
        if (type === 'essential') return; // Essential cannot be disabled
        setConsentData(prev => ({ ...prev, [type]: value }));
    };

    const submitConsent = () => {
        // Submit consent preferences
        const formData = {
            type: 'privacy_policy',
            version: '1.0',
            data_types: Object.keys(consentData).filter(key => consentData[key as keyof typeof consentData]),
            processing_purposes: Object.keys(consentData).filter(key => consentData[key as keyof typeof consentData])
        };
        
        // This would normally be submitted via Inertia
        console.log('Consent data:', formData);
    };

    return (
        <GuestLayout>
            <Head title="Privacy Policy" />

            <div className="min-h-screen bg-gray-50 py-12">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="bg-white shadow-sm rounded-lg p-8">
                        <h1 className="text-3xl font-bold text-gray-900 mb-8">Privacy Policy</h1>
                        
                        <div className="prose max-w-none">
                            <p className="text-gray-600 mb-6">
                                Last updated: {new Date().toLocaleDateString()}
                            </p>

                            <section className="mb-8">
                                <h2 className="text-2xl font-semibold text-gray-900 mb-4">1. Information We Collect</h2>
                                <p className="text-gray-700 mb-4">
                                    We collect information you provide directly to us, such as when you create an account, 
                                    make a purchase, or contact us for support.
                                </p>
                                <ul className="list-disc list-inside text-gray-700 space-y-2">
                                    <li>Personal information (name, email, phone number)</li>
                                    <li>Payment information (processed securely by our payment partners)</li>
                                    <li>Shipping addresses</li>
                                    <li>Order history and preferences</li>
                                    <li>Communications with our support team</li>
                                </ul>
                            </section>

                            <section className="mb-8">
                                <h2 className="text-2xl font-semibold text-gray-900 mb-4">2. How We Use Your Information</h2>
                                <p className="text-gray-700 mb-4">
                                    We use the information we collect to:
                                </p>
                                <ul className="list-disc list-inside text-gray-700 space-y-2">
                                    <li>Process and fulfill your orders</li>
                                    <li>Communicate with you about your account and orders</li>
                                    <li>Provide customer support</li>
                                    <li>Improve our products and services</li>
                                    <li>Send marketing communications (with your consent)</li>
                                    <li>Comply with legal obligations</li>
                                </ul>
                            </section>

                            <section className="mb-8">
                                <h2 className="text-2xl font-semibold text-gray-900 mb-4">3. Information Sharing</h2>
                                <p className="text-gray-700 mb-4">
                                    We do not sell, trade, or otherwise transfer your personal information to third parties 
                                    except as described in this policy:
                                </p>
                                <ul className="list-disc list-inside text-gray-700 space-y-2">
                                    <li>Service providers who assist in our operations</li>
                                    <li>Payment processors for transaction processing</li>
                                    <li>Shipping companies for order fulfillment</li>
                                    <li>Legal authorities when required by law</li>
                                </ul>
                            </section>

                            <section className="mb-8">
                                <h2 className="text-2xl font-semibold text-gray-900 mb-4">4. Your Rights (GDPR)</h2>
                                <p className="text-gray-700 mb-4">
                                    Under the General Data Protection Regulation, you have the following rights:
                                </p>
                                <ul className="list-disc list-inside text-gray-700 space-y-2">
                                    <li><strong>Right to Access:</strong> Request a copy of your personal data</li>
                                    <li><strong>Right to Rectification:</strong> Correct inaccurate personal data</li>
                                    <li><strong>Right to Erasure:</strong> Request deletion of your personal data</li>
                                    <li><strong>Right to Portability:</strong> Receive your data in a portable format</li>
                                    <li><strong>Right to Object:</strong> Object to processing of your personal data</li>
                                    <li><strong>Right to Restriction:</strong> Restrict processing of your personal data</li>
                                </ul>
                                <p className="text-gray-700 mt-4">
                                    To exercise these rights, please visit your <a href="/privacy/dashboard" className="text-blue-600 hover:underline">Privacy Dashboard</a> 
                                    or contact us at privacy@smartmart.com.
                                </p>
                            </section>

                            <section className="mb-8">
                                <h2 className="text-2xl font-semibold text-gray-900 mb-4">5. Data Retention</h2>
                                <p className="text-gray-700 mb-4">
                                    We retain your personal information for as long as necessary to fulfill the purposes 
                                    outlined in this policy, unless a longer retention period is required by law.
                                </p>
                                <ul className="list-disc list-inside text-gray-700 space-y-2">
                                    <li>Account data: Until account deletion</li>
                                    <li>Order data: 7 years for tax and legal purposes</li>
                                    <li>Marketing data: Until consent is withdrawn</li>
                                    <li>Analytics data: 24 months</li>
                                </ul>
                            </section>

                            <section className="mb-8">
                                <h2 className="text-2xl font-semibold text-gray-900 mb-4">6. Security</h2>
                                <p className="text-gray-700 mb-4">
                                    We implement appropriate technical and organizational measures to protect your personal 
                                    information against unauthorized access, alteration, disclosure, or destruction.
                                </p>
                            </section>

                            <section className="mb-8">
                                <h2 className="text-2xl font-semibold text-gray-900 mb-4">7. Contact Information</h2>
                                <p className="text-gray-700 mb-4">
                                    If you have questions about this Privacy Policy, please contact us at:
                                </p>
                                <div className="bg-gray-50 p-4 rounded-md">
                                    <p className="text-gray-700">
                                        <strong>Email:</strong> privacy@smartmart.com<br />
                                        <strong>Address:</strong> SmartMart Privacy Team<br />
                                        123 Commerce Street<br />
                                        City, State 12345
                                    </p>
                                </div>
                            </section>
                        </div>

                        {/* Consent Management Section */}
                        <div className="mt-12 border-t pt-8">
                            <h2 className="text-2xl font-semibold text-gray-900 mb-6">Manage Your Preferences</h2>
                            <div className="space-y-4">
                                <div className="flex items-center justify-between p-4 border rounded-lg">
                                    <div>
                                        <h3 className="font-medium">Essential Cookies</h3>
                                        <p className="text-sm text-gray-600">Required for basic site functionality</p>
                                    </div>
                                    <div className="text-sm text-gray-500">Always Active</div>
                                </div>

                                <div className="flex items-center justify-between p-4 border rounded-lg">
                                    <div>
                                        <h3 className="font-medium">Analytics</h3>
                                        <p className="text-sm text-gray-600">Help us understand how visitors use our site</p>
                                    </div>
                                    <label className="flex items-center">
                                        <input
                                            type="checkbox"
                                            checked={consentData.analytics}
                                            onChange={(e) => handleConsentChange('analytics', e.target.checked)}
                                            className="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        />
                                    </label>
                                </div>

                                <div className="flex items-center justify-between p-4 border rounded-lg">
                                    <div>
                                        <h3 className="font-medium">Marketing</h3>
                                        <p className="text-sm text-gray-600">Personalized ads and email communications</p>
                                    </div>
                                    <label className="flex items-center">
                                        <input
                                            type="checkbox"
                                            checked={consentData.marketing}
                                            onChange={(e) => handleConsentChange('marketing', e.target.checked)}
                                            className="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        />
                                    </label>
                                </div>
                            </div>

                            <div className="mt-6">
                                <PrimaryButton onClick={submitConsent}>
                                    Save Preferences
                                </PrimaryButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </GuestLayout>
    );
}