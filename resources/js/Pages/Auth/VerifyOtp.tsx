import React, { useState } from 'react';
import { Head, Link, Form } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import PrimaryButton from '@/Components/PrimaryButton';

interface Props {
    status?: string;
}

export default function VerifyOtp({ status }: Props) {
    const [step, setStep] = useState<'send' | 'verify'>('send');
    const [emailForVerification, setEmailForVerification] = useState('');

    const handleSendSuccess = () => {
        setStep('verify');
    };

    const handleResendOtp = () => {
        // We'll handle this with a separate form submission
        if (emailForVerification) {
            const form = document.getElementById('resend-form') as HTMLFormElement;
            if (form) form.submit();
        }
    };

    return (
        <GuestLayout>
            <Head title="Verify OTP" />

            <div className="mb-4 text-sm text-gray-600">
                {step === 'send' 
                    ? 'Enter your email address to receive a verification code.'
                    : 'Enter the verification code sent to your email or phone.'}
            </div>

            {status && (
                <div className="mb-4 font-medium text-sm text-green-600">
                    {status}
                </div>
            )}

            {step === 'send' ? (
                <Form action={route('otp.send')} method="post" onSuccess={() => handleSendSuccess()}>
                    {({ errors, processing }) => (
                        <>
                            <div>
                                <label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-1">
                                    Email
                                </label>
                                <input
                                    id="email"
                                    type="email"
                                    name="email"
                                    className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    onChange={(e: React.ChangeEvent<HTMLInputElement>) => 
                                        setEmailForVerification(e.target.value)
                                    }
                                    required
                                />
                                {errors.email && (
                                    <div className="mt-2 text-sm text-red-600">
                                        {errors.email}
                                    </div>
                                )}
                            </div>

                            <div className="mt-4">
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Delivery Method
                                </label>
                                <div className="mt-2 space-x-4">
                                    <label className="inline-flex items-center">
                                        <input
                                            type="radio"
                                            name="type"
                                            value="email"
                                            defaultChecked
                                            className="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                        />
                                        <span className="ml-2 text-sm text-gray-600">Email</span>
                                    </label>
                                    <label className="inline-flex items-center">
                                        <input
                                            type="radio"
                                            name="type"
                                            value="sms"
                                            className="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                        />
                                        <span className="ml-2 text-sm text-gray-600">SMS</span>
                                    </label>
                                </div>
                            </div>

                            <div className="flex items-center justify-between mt-4">
                                <Link
                                    href={route('login')}
                                    className="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    Back to Login
                                </Link>

                                <PrimaryButton className="ml-4" disabled={processing}>
                                    Send OTP
                                </PrimaryButton>
                            </div>
                        </>
                    )}
                </Form>
            ) : (
                <>
                    <Form action={route('otp.verify')} method="post">
                        {({ errors, processing }) => (
                            <>
                                <input type="hidden" name="email" value={emailForVerification} />
                                <div>
                                    <label htmlFor="otp" className="block text-sm font-medium text-gray-700 mb-1">
                                        Verification Code
                                    </label>
                                    <input
                                        id="otp"
                                        type="text"
                                        name="otp"
                                        className="mt-1 block w-full text-center text-2xl tracking-widest border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                        placeholder="000000"
                                        maxLength={6}
                                        onChange={(e: React.ChangeEvent<HTMLInputElement>) => {
                                            const value = e.target.value.replace(/\D/g, '').slice(0, 6);
                                            e.target.value = value;
                                        }}
                                        required
                                    />
                                    {errors.otp && (
                                        <div className="mt-2 text-sm text-red-600">
                                            {errors.otp}
                                        </div>
                                    )}
                                </div>

                                <div className="flex items-center justify-between mt-4">
                                    <div className="flex flex-col space-y-2">
                                        <button
                                            type="button"
                                            onClick={() => setStep('send')}
                                            className="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                        >
                                            Change Email
                                        </button>
                                        <button
                                            type="button"
                                            onClick={handleResendOtp}
                                            className="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                        >
                                            Resend Code
                                        </button>
                                    </div>

                                    <PrimaryButton disabled={processing}>
                                        Verify
                                    </PrimaryButton>
                                </div>
                            </>
                        )}
                    </Form>
                    
                    {/* Hidden form for resending OTP */}
                    <form id="resend-form" action={route('otp.send')} method="post" style={{display: 'none'}}>
                        <input type="hidden" name="email" value={emailForVerification} />
                        <input type="hidden" name="type" value="email" />
                    </form>
                </>
            )}
        </GuestLayout>
    );
}