import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Form, Link } from '@inertiajs/react';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';

interface DataRequest {
    id: number;
    user: {
        id: number;
        name: string;
        email: string;
    };
    type: string;
    status: string;
    reason?: string;
    admin_notes?: string;
    requested_at: string;
    processed_at?: string;
    completed_at?: string;
    expires_at?: string;
}

interface Props {
    requests: {
        data: DataRequest[];
        meta: any;
    };
    filters: {
        status?: string;
        type?: string;
        search?: string;
    };
}

export default function GdprRequests({ requests, filters }: Props) {
    const [selectedRequest, setSelectedRequest] = useState<DataRequest | null>(null);
    const [adminNotes, setAdminNotes] = useState('');
    const [actionType, setActionType] = useState<'approve' | 'reject' | null>(null);

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

    const getTypeBadge = (type: string) => {
        const baseClasses = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium';
        switch (type) {
            case 'export':
                return `${baseClasses} bg-blue-100 text-blue-800`;
            case 'delete':
                return `${baseClasses} bg-red-100 text-red-800`;
            case 'rectification':
                return `${baseClasses} bg-yellow-100 text-yellow-800`;
            case 'portability':
                return `${baseClasses} bg-purple-100 text-purple-800`;
            default:
                return `${baseClasses} bg-gray-100 text-gray-800`;
        }
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    const handleAction = (request: DataRequest, action: 'approve' | 'reject') => {
        setSelectedRequest(request);
        setActionType(action);
        setAdminNotes(request.admin_notes || '');
    };

    const submitAction = () => {
        if (!selectedRequest || !actionType) return;

        const formData = {
            action: actionType,
            admin_notes: adminNotes,
            _method: 'PATCH'
        };

        // Submit via Inertia
        // router.patch(route('admin.gdpr.update', selectedRequest.id), formData);
        console.log('Action:', formData);
        
        setSelectedRequest(null);
        setActionType(null);
        setAdminNotes('');
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">GDPR Requests</h2>}
        >
            <Head title="GDPR Requests" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        
                        {/* Filters */}
                        <div className="p-6 border-b border-gray-200">
                            <Form action={route('admin.gdpr.index')} method="get" className="flex flex-wrap items-end gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                    <select
                                        name="status"
                                        defaultValue={filters.status || ''}
                                        className="border border-gray-300 rounded-md px-3 py-2 text-sm"
                                    >
                                        <option value="">All Statuses</option>
                                        <option value="pending">Pending</option>
                                        <option value="processing">Processing</option>
                                        <option value="completed">Completed</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Type</label>
                                    <select
                                        name="type"
                                        defaultValue={filters.type || ''}
                                        className="border border-gray-300 rounded-md px-3 py-2 text-sm"
                                    >
                                        <option value="">All Types</option>
                                        <option value="export">Data Export</option>
                                        <option value="delete">Account Deletion</option>
                                        <option value="rectification">Data Rectification</option>
                                        <option value="portability">Data Portability</option>
                                    </select>
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                    <input
                                        type="text"
                                        name="search"
                                        defaultValue={filters.search || ''}
                                        placeholder="Search by email or name..."
                                        className="border border-gray-300 rounded-md px-3 py-2 text-sm"
                                    />
                                </div>

                                <PrimaryButton type="submit">Filter</PrimaryButton>
                                
                                {(filters.status || filters.type || filters.search) && (
                                    <Link
                                        href={route('admin.gdpr.index')}
                                        className="text-gray-600 hover:text-gray-900 text-sm"
                                    >
                                        Clear Filters
                                    </Link>
                                )}
                            </Form>
                        </div>

                        {/* Requests Table */}
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            User
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Type
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Requested
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {requests.data.map((request) => (
                                        <tr key={request.id} className="hover:bg-gray-50">
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div>
                                                    <div className="text-sm font-medium text-gray-900">
                                                        {request.user.name}
                                                    </div>
                                                    <div className="text-sm text-gray-500">
                                                        {request.user.email}
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className={getTypeBadge(request.type)}>
                                                    {request.type}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className={getStatusBadge(request.status)}>
                                                    {request.status}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {formatDate(request.requested_at)}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                                {request.status === 'pending' && (
                                                    <>
                                                        <button
                                                            onClick={() => handleAction(request, 'approve')}
                                                            className="text-green-600 hover:text-green-900"
                                                        >
                                                            Approve
                                                        </button>
                                                        <button
                                                            onClick={() => handleAction(request, 'reject')}
                                                            className="text-red-600 hover:text-red-900"
                                                        >
                                                            Reject
                                                        </button>
                                                    </>
                                                )}
                                                <Link
                                                    href={route('admin.gdpr.show', request.id)}
                                                    className="text-blue-600 hover:text-blue-900"
                                                >
                                                    View
                                                </Link>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        {/* Pagination would go here */}
                    </div>
                </div>
            </div>

            {/* Action Modal */}
            {selectedRequest && actionType && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
                    <div className="bg-white rounded-lg max-w-md w-full p-6">
                        <h3 className="text-lg font-medium mb-4">
                            {actionType === 'approve' ? 'Approve' : 'Reject'} Request
                        </h3>
                        <div className="mb-4">
                            <p className="text-sm text-gray-600 mb-2">
                                <strong>User:</strong> {selectedRequest.user.name} ({selectedRequest.user.email})
                            </p>
                            <p className="text-sm text-gray-600 mb-2">
                                <strong>Type:</strong> {selectedRequest.type}
                            </p>
                            {selectedRequest.reason && (
                                <p className="text-sm text-gray-600 mb-2">
                                    <strong>Reason:</strong> {selectedRequest.reason}
                                </p>
                            )}
                        </div>
                        
                        <div className="mb-4">
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                Admin Notes
                            </label>
                            <textarea
                                value={adminNotes}
                                onChange={(e) => setAdminNotes(e.target.value)}
                                placeholder="Add notes about this decision..."
                                className="w-full border border-gray-300 rounded-md px-3 py-2"
                                rows={3}
                            />
                        </div>

                        <div className="flex justify-end space-x-3">
                            <SecondaryButton
                                onClick={() => {
                                    setSelectedRequest(null);
                                    setActionType(null);
                                    setAdminNotes('');
                                }}
                            >
                                Cancel
                            </SecondaryButton>
                            <PrimaryButton
                                onClick={submitAction}
                                className={actionType === 'reject' ? 'bg-red-600 hover:bg-red-700' : ''}
                            >
                                {actionType === 'approve' ? 'Approve' : 'Reject'}
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}