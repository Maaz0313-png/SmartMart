<?php

return [
    /*
    |--------------------------------------------------------------------------
    | GDPR Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for GDPR compliance features including data
    | retention, processing timeframes, and consent management.
    |
    */

    // Data retention periods (in days)
    'retention' => [
        'user_data' => 2555, // 7 years
        'order_data' => 2555, // 7 years for tax purposes
        'analytics_data' => 730, // 2 years
        'marketing_data' => 1095, // 3 years
        'audit_logs' => 2190, // 6 years
        'export_files' => 30, // Export download availability
    ],

    // Request processing timeframes (in days)
    'processing_time' => [
        'data_export' => 30,
        'data_deletion' => 30,
        'data_rectification' => 30,
        'data_portability' => 30,
    ],

    // Auto-approve settings
    'auto_approve' => [
        'data_export' => false, // Require manual approval
        'data_deletion' => false, // Require manual approval
        'data_rectification' => false,
        'data_portability' => false,
    ],

    // Notification settings
    'notifications' => [
        'admin_email' => env('GDPR_ADMIN_EMAIL', 'privacy@smartmart.com'),
        'notify_on_request' => true,
        'notify_on_overdue' => true,
        'overdue_threshold_days' => 25, // Warn 5 days before deadline
    ],

    // Data export settings
    'export' => [
        'format' => 'json', // json, csv, xml
        'include_metadata' => true,
        'encrypt_files' => false,
        'max_file_size_mb' => 100,
    ],

    // Data deletion settings
    'deletion' => [
        'soft_delete' => true, // Use soft deletes initially
        'anonymize_instead' => true, // Anonymize rather than hard delete
        'preserve_business_data' => true, // Keep order totals, etc.
        'delete_after_days' => 90, // Days before hard delete after soft delete
    ],

    // Consent management
    'consent' => [
        'required_types' => ['essential', 'privacy_policy'],
        'optional_types' => ['marketing', 'analytics', 'cookies'],
        'version' => '1.0',
        'record_ip' => true,
        'record_user_agent' => true,
    ],

    // Audit and compliance
    'audit' => [
        'log_all_requests' => true,
        'log_admin_actions' => true,
        'detailed_logging' => true,
        'retention_days' => 2555, // 7 years
    ],

    // Security settings
    'security' => [
        'require_user_confirmation' => true,
        'admin_approval_required' => true,
        'rate_limit_requests' => true,
        'max_requests_per_user_per_year' => 5,
    ],

    // Legal information
    'legal' => [
        'data_controller' => 'SmartMart Ltd.',
        'dpo_email' => env('DPO_EMAIL', 'dpo@smartmart.com'),
        'privacy_policy_url' => '/privacy-policy',
        'contact_address' => '123 Commerce Street, City, State 12345',
    ],
];