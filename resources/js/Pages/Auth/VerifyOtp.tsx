import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Link } from '@inertiajs/react';

interface Props {
    status?: string;
}

export default function VerifyOtp({ status }: Props) {
    const [otpType, setOtpType] = useState<'email' | 'sms'>('email');
    const [step, setStep] = useState<'send' | 'verify'>('send');
    
    const sendForm = useForm({
        email: '',
        type: 'email' as 'email' | 'sms',
    });
    
    const verifyForm = useForm({
        email: '',
        otp: '',
    });

    const handleSendOtp = (e: React.FormEvent) => {
        e.preventDefault();
        sendForm.post(route('otp.send'), {
            onSuccess: () => {
                setStep('verify');
                verifyForm.setData('email', sendForm.data.email);
            },
        });
    };

    const handleVerifyOtp = (e: React.FormEvent) => {
        e.preventDefault();
        verifyForm.post(route('otp.verify'));
    };

    const handleResendOtp = () => {
        sendForm.post(route('otp.send'));
    };

    return (
        <GuestLayout>
            <Head title=\"Verify OTP\" />

            <div className=\"mb-4 text-sm text-gray-600\">
                {step === 'send' 
                    ? 'Enter your email address to receive a verification code.'
                    : 'Enter the verification code sent to your email or phone.'}
            </div>

            {status && (
                <div className=\"mb-4 font-medium text-sm text-green-600\">
                    {status}
                </div>
            )}

            {step === 'send' ? (
                <form onSubmit={handleSendOtp}>
                    <div>
                        <InputLabel htmlFor=\"email\" value=\"Email\" />
                        <TextInput
                            id=\"email\"
                            type=\"email\"
                            name=\"email\"
                            value={sendForm.data.email}
                            className=\"mt-1 block w-full\"
                            isFocused={true}
                            onChange={(e) => sendForm.setData('email', e.target.value)}
                            required
                        />
                        <InputError message={sendForm.errors.email} className=\"mt-2\" />
                    </div>

                    <div className=\"mt-4\">
                        <InputLabel value=\"Delivery Method\" />
                        <div className=\"mt-2 space-x-4\">
                            <label className=\"inline-flex items-center\">
                                <input
                                    type=\"radio\"
                                    name=\"type\"
                                    value=\"email\"
                                    checked={sendForm.data.type === 'email'}
                                    onChange={(e) => sendForm.setData('type', e.target.value as 'email' | 'sms')}
                                    className=\"rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500\"
                                />
                                <span className=\"ml-2 text-sm text-gray-600\">Email</span>
                            </label>
                            <label className=\"inline-flex items-center\">
                                <input
                                    type=\"radio\"
                                    name=\"type\"
                                    value=\"sms\"
                                    checked={sendForm.data.type === 'sms'}
                                    onChange={(e) => sendForm.setData('type', e.target.value as 'email' | 'sms')}
                                    className=\"rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500\"
                                />
                                <span className=\"ml-2 text-sm text-gray-600\">SMS</span>
                            </label>
                        </div>
                    </div>

                    <div className=\"flex items-center justify-between mt-4\">
                        <Link
                            href={route('login')}
                            className=\"underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500\"
                        >
                            Back to Login
                        </Link>

                        <PrimaryButton className=\"ml-4\" disabled={sendForm.processing}>
                            Send OTP
                        </PrimaryButton>
                    </div>
                </form>
            ) : (
                <form onSubmit={handleVerifyOtp}>
                    <div>
                        <InputLabel htmlFor=\"otp\" value=\"Verification Code\" />
                        <TextInput
                            id=\"otp\"
                            type=\"text\"
                            name=\"otp\"
                            value={verifyForm.data.otp}
                            className=\"mt-1 block w-full text-center text-2xl tracking-widest\"
                            placeholder=\"000000\"
                            maxLength={6}
                            isFocused={true}
                            onChange={(e) => {
                                const value = e.target.value.replace(/\\D/g, '').slice(0, 6);
                                verifyForm.setData('otp', value);
                            }}
                            required
                        />
                        <InputError message={verifyForm.errors.otp} className=\"mt-2\" />
                    </div>

                    <div className=\"flex items-center justify-between mt-4\">
                        <div className=\"flex flex-col space-y-2\">
                            <button
                                type=\"button\"
                                onClick={() => setStep('send')}
                                className=\"underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500\"
                            >
                                Change Email
                            </button>
                            <button
                                type=\"button\"
                                onClick={handleResendOtp}
                                disabled={sendForm.processing}
                                className=\"underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500\"
                            >
                                Resend Code
                            </button>
                        </div>

                        <PrimaryButton disabled={verifyForm.processing || verifyForm.data.otp.length !== 6}>
                            Verify
                        </PrimaryButton>
                    </div>
                </form>
            )}
        </GuestLayout>
    );
}