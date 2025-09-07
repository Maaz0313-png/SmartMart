import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Form } from '@inertiajs/react';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';

interface DataRequest {
    id: number;
    type: string;
    status: string;
    requested_at: string;
    completed_at?: string;
    expires_at?: string;
    file_path?: string;
}

interface Agreement {
    id: number;
    type: string;
    version: string;
    agreed_at: string;
    data_types: string[];
    processing_purposes: string[];
}

interface Props {
    dataRequests: DataRequest[];
    agreements: Agreement[];
}

export default function Dashboard({ dataRequests, agreements }: Props) {
    const [showExportForm, setShowExportForm] = useState(false);
    const [showDeleteForm, setShowDeleteForm] = useState(false);
    const [deleteReason, setDeleteReason] = useState('');

    const getStatusBadge = (status: string) => {
        const baseClasses = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium';
        switch (status) {
            case 'completed':
                return `${baseClasses} bg-green-100 text-green-800`;
            case 'processing':
                return `${baseClasses} bg-yellow-100 text-yellow-800`;
            case 'pending':
                return `${baseClasses} bg-blue-100 text-blue-800`;
            case 'rejected':
                return `${baseClasses} bg-red-100 text-red-800`;
            default:
                return `${baseClasses} bg-gray-100 text-gray-800`;
        }
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Privacy Dashboard</h2>}
        >
            <Head title="Privacy Dashboard" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    
                    {/* Quick Actions */}
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <h3 className="text-lg font-medium mb-4">Data Rights</h3>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="border rounded-lg p-4">
                                    <h4 className="font-medium mb-2">Export My Data</h4>
                                    <p className="text-gray-600 text-sm mb-3">
                                        Download all your personal data in a portable format.
                                    </p>
                                    <PrimaryButton
                                        onClick={() => setShowExportForm(true)}
                                        disabled={dataRequests.some(req => req.type === 'export' && ['pending', 'processing'].includes(req.status))}
                                    >
                                        Request Data Export
                                    </PrimaryButton>
                                </div>

                                <div className="border rounded-lg p-4">
                                    <h4 className="font-medium mb-2">Delete My Account</h4>
                                    <p className="text-gray-600 text-sm mb-3">
                                        Permanently delete your account and all associated data.
                                    </p>
                                    <SecondaryButton
                                        onClick={() => setShowDeleteForm(true)}
                                        className="border-red-300 text-red-700 hover:bg-red-50"
                                    >
                                        Request Account Deletion
                                    </SecondaryButton>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Data Requests History */}
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <h3 className="text-lg font-medium mb-4">Request History</h3>
                            {dataRequests.length === 0 ? (
                                <p className="text-gray-500">No data requests found.</p>
                            ) : (
                                <div className="space-y-4">
                                    {dataRequests.map((request) => (
                                        <div key={request.id} className="border rounded-lg p-4">
                                            <div className="flex items-center justify-between">
                                                <div>
                                                    <div className="flex items-center space-x-2">
                                                        <span className="font-medium capitalize">{request.type} Request</span>
                                                        <span className={getStatusBadge(request.status)}>{request.status}</span>
                                                    </div>
                                                    <p className="text-gray-600 text-sm">
                                                        Requested on {formatDate(request.requested_at)}
                                                    </p>
                                                    {request.completed_at && (
                                                        <p className="text-gray-600 text-sm">
                                                            Completed on {formatDate(request.completed_at)}
                                                        </p>
                                                    )}
                                                </div>
                                                
                                                {request.status === 'completed' && request.type === 'export' && request.file_path && (
                                                    <div>
                                                        {request.expires_at && new Date(request.expires_at) > new Date() ? (
                                                            <a
                                                                href={route('gdpr.download', request.id)}
                                                                className="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                                            >
                                                                Download Data
                                                            </a>
                                                        ) : (
                                                            <span className="text-gray-500 text-sm">Download expired</span>
                                                        )}
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Consent Management */}
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <h3 className="text-lg font-medium mb-4">Consent Management</h3>
                            {agreements.length === 0 ? (
                                <p className="text-gray-500">No consent agreements found.</p>
                            ) : (
                                <div className="space-y-4">
                                    {agreements.map((agreement) => (
                                        <div key={agreement.id} className="border rounded-lg p-4">
                                            <div className="flex items-center justify-between">
                                                <div>
                                                    <h4 className="font-medium capitalize">{agreement.type} Consent</h4>
                                                    <p className="text-gray-600 text-sm">
                                                        Agreed on {formatDate(agreement.agreed_at)}
                                                    </p>
                                                    <p className="text-gray-600 text-sm">
                                                        Version: {agreement.version}
                                                    </p>
                                                    <div className="mt-2">
                                                        <p className="text-gray-600 text-sm">
                                                            <span className="font-medium">Data Types:</span> {agreement.data_types.join(', ')}
                                                        </p>
                                                        <p className="text-gray-600 text-sm">
                                                            <span className="font-medium">Purposes:</span> {agreement.processing_purposes.join(', ')}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            {/* Export Data Modal */}
            {showExportForm && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
                    <div className="bg-white rounded-lg max-w-md w-full p-6">
                        <h3 className="text-lg font-medium mb-4">Request Data Export</h3>
                        <p className="text-gray-600 mb-4">
                            We'll compile all your personal data and send you a download link via email.
                            This process may take up to 30 days.
                        </p>
                        <Form action={route('gdpr.export')} method="post">
                            <div className="flex justify-end space-x-3">
                                <SecondaryButton
                                    type="button"
                                    onClick={() => setShowExportForm(false)}
                                >
                                    Cancel
                                </SecondaryButton>
                                <PrimaryButton type="submit">
                                    Submit Request
                                </PrimaryButton>
                            </div>
                        </Form>
                    </div>
                </div>
            )}

            {/* Delete Account Modal */}
            {showDeleteForm && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
                    <div className="bg-white rounded-lg max-w-md w-full p-6">
                        <h3 className="text-lg font-medium mb-4 text-red-600">Request Account Deletion</h3>
                        <p className="text-gray-600 mb-4">
                            This action will permanently delete your account and all associated data.
                            Please provide a reason for this request.
                        </p>
                        <Form action={route('gdpr.delete')} method="post">
                            <textarea
                                name="reason"
                                value={deleteReason}
                                onChange={(e) => setDeleteReason(e.target.value)}
                                placeholder="Please tell us why you want to delete your account..."
                                className="w-full border border-gray-300 rounded-md px-3 py-2 mb-4"
                                rows={3}
                                required
                            />
                            <label className="flex items-center mb-4">
                                <input
                                    type="checkbox"
                                    name="confirmation"
                                    className="mr-2"
                                    required
                                />
                                <span className="text-sm text-gray-600">
                                    I understand this action is irreversible
                                </span>
                            </label>
                            <div className="flex justify-end space-x-3">
                                <SecondaryButton
                                    type="button"
                                    onClick={() => {
                                        setShowDeleteForm(false);
                                        setDeleteReason('');
                                    }}
                                >
                                    Cancel
                                </SecondaryButton>
                                <PrimaryButton 
                                    type="submit"
                                    className="bg-red-600 hover:bg-red-700"
                                >
                                    Submit Request
                                </PrimaryButton>
                            </div>
                        </Form>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}