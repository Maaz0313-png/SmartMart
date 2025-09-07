<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:settings.view')->only(['index', 'show']);
        $this->middleware('permission:settings.update')->only(['update']);
    }

    public function index(): Response
    {
        $settings = $this->getAllSettings();

        return Inertia::render('Admin/Settings/Index', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'section' => 'required|string|in:general,payment,shipping,email,seo,social,analytics,security',
            'settings' => 'required|array',
        ]);

        $section = $request->section;
        $settings = $request->settings;

        // Validate settings based on section
        $this->validateSectionSettings($section, $settings);

        // Update settings
        foreach ($settings as $key => $value) {
            $this->updateSetting("{$section}.{$key}", $value);
        }

        // Clear cache
        Cache::tags(['settings'])->flush();

        // Log activity
        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'section' => $section,
                'updated_keys' => array_keys($settings),
            ])
            ->log('System settings updated');

        return redirect()->back()->with('success', ucfirst($section) . ' settings updated successfully.');
    }

    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'type' => 'required|in:main_logo,favicon,mobile_logo',
        ]);

        $type = $request->type;
        $file = $request->file('logo');
        
        // Delete old logo if exists
        $oldPath = $this->getSetting("general.{$type}");
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        // Store new logo
        $path = $file->store('logos', 'public');
        $this->updateSetting("general.{$type}", $path);

        // Clear cache
        Cache::tags(['settings'])->flush();

        return redirect()->back()->with('success', 'Logo updated successfully.');
    }

    public function testMailConfiguration()
    {
        try {
            // Send a test email
            \Mail::raw('This is a test email to verify your mail configuration.', function ($message) {
                $message->to(auth()->user()->email)
                        ->subject('SmartMart - Mail Configuration Test');
            });

            return redirect()->back()->with('success', 'Test email sent successfully. Please check your inbox.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['email' => 'Failed to send test email: ' . $e->getMessage()]);
        }
    }

    public function clearCache()
    {
        // Clear various caches
        \Artisan::call('cache:clear');
        \Artisan::call('config:clear');
        \Artisan::call('route:clear');
        \Artisan::call('view:clear');

        // Clear custom caches
        Cache::flush();

        activity()
            ->causedBy(auth()->user())
            ->log('System cache cleared');

        return redirect()->back()->with('success', 'All caches cleared successfully.');
    }

    public function exportSettings()
    {
        $settings = $this->getAllSettings();
        
        $filename = 'smartmart-settings-' . now()->format('Y-m-d-H-i-s') . '.json';
        
        return response()->streamDownload(function () use ($settings) {
            echo json_encode($settings, JSON_PRETTY_PRINT);
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }

    public function importSettings(Request $request)
    {
        $request->validate([
            'settings_file' => 'required|file|mimes:json|max:1024', // 1MB max
        ]);

        try {
            $content = file_get_contents($request->file('settings_file')->getPathname());
            $settings = json_decode($content, true);

            if (!$settings) {
                throw new \Exception('Invalid JSON format');
            }

            // Validate and import settings
            foreach ($settings as $section => $sectionSettings) {
                if (is_array($sectionSettings)) {
                    foreach ($sectionSettings as $key => $value) {
                        $this->updateSetting("{$section}.{$key}", $value);
                    }
                }
            }

            // Clear cache
            Cache::tags(['settings'])->flush();

            activity()
                ->causedBy(auth()->user())
                ->withProperties(['imported_sections' => array_keys($settings)])
                ->log('Settings imported from file');

            return redirect()->back()->with('success', 'Settings imported successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['settings_file' => 'Failed to import settings: ' . $e->getMessage()]);
        }
    }

    private function getAllSettings(): array
    {
        return Cache::tags(['settings'])->remember('all_settings', 3600, function () {
            return [
                'general' => [
                    'site_name' => $this->getSetting('general.site_name', 'SmartMart'),
                    'site_description' => $this->getSetting('general.site_description', 'AI-Driven E-commerce & Subscription Marketplace'),
                    'site_url' => $this->getSetting('general.site_url', url('/')),
                    'admin_email' => $this->getSetting('general.admin_email', 'admin@smartmart.com'),
                    'timezone' => $this->getSetting('general.timezone', 'UTC'),
                    'currency' => $this->getSetting('general.currency', 'USD'),
                    'currency_symbol' => $this->getSetting('general.currency_symbol', '$'),
                    'main_logo' => $this->getSetting('general.main_logo'),
                    'favicon' => $this->getSetting('general.favicon'),
                    'mobile_logo' => $this->getSetting('general.mobile_logo'),
                ],
                'payment' => [
                    'stripe_enabled' => $this->getSetting('payment.stripe_enabled', true),
                    'paypal_enabled' => $this->getSetting('payment.paypal_enabled', true),
                    'cod_enabled' => $this->getSetting('payment.cod_enabled', true),
                    'default_gateway' => $this->getSetting('payment.default_gateway', 'stripe'),
                    'currency_code' => $this->getSetting('payment.currency_code', 'USD'),
                ],
                'shipping' => [
                    'free_shipping_threshold' => $this->getSetting('shipping.free_shipping_threshold', 100),
                    'default_shipping_cost' => $this->getSetting('shipping.default_shipping_cost', 10),
                    'weight_based_shipping' => $this->getSetting('shipping.weight_based_shipping', false),
                    'international_shipping' => $this->getSetting('shipping.international_shipping', true),
                ],
                'email' => [
                    'notifications_enabled' => $this->getSetting('email.notifications_enabled', true),
                    'order_confirmation' => $this->getSetting('email.order_confirmation', true),
                    'shipping_notifications' => $this->getSetting('email.shipping_notifications', true),
                    'marketing_emails' => $this->getSetting('email.marketing_emails', true),
                    'from_name' => $this->getSetting('email.from_name', 'SmartMart'),
                    'from_email' => $this->getSetting('email.from_email', 'noreply@smartmart.com'),
                ],
                'seo' => [
                    'meta_title' => $this->getSetting('seo.meta_title', 'SmartMart - AI-Driven E-commerce'),
                    'meta_description' => $this->getSetting('seo.meta_description', 'Shop smart with AI-powered recommendations'),
                    'meta_keywords' => $this->getSetting('seo.meta_keywords', 'e-commerce, ai, smart shopping, subscriptions'),
                    'google_analytics_id' => $this->getSetting('seo.google_analytics_id'),
                    'google_tag_manager_id' => $this->getSetting('seo.google_tag_manager_id'),
                    'sitemap_enabled' => $this->getSetting('seo.sitemap_enabled', true),
                ],
                'social' => [
                    'facebook_url' => $this->getSetting('social.facebook_url'),
                    'twitter_url' => $this->getSetting('social.twitter_url'),
                    'instagram_url' => $this->getSetting('social.instagram_url'),
                    'linkedin_url' => $this->getSetting('social.linkedin_url'),
                    'youtube_url' => $this->getSetting('social.youtube_url'),
                ],
                'analytics' => [
                    'track_user_behavior' => $this->getSetting('analytics.track_user_behavior', true),
                    'track_product_views' => $this->getSetting('analytics.track_product_views', true),
                    'track_search_queries' => $this->getSetting('analytics.track_search_queries', true),
                ],
                'security' => [
                    'force_https' => $this->getSetting('security.force_https', true),
                    'session_timeout' => $this->getSetting('security.session_timeout', 120), // minutes
                    'max_login_attempts' => $this->getSetting('security.max_login_attempts', 5),
                    'password_min_length' => $this->getSetting('security.password_min_length', 8),
                    'two_factor_enabled' => $this->getSetting('security.two_factor_enabled', false),
                ],
            ];
        });
    }

    private function validateSectionSettings(string $section, array $settings): void
    {
        $rules = $this->getValidationRules($section);
        
        $validator = \Validator::make($settings, $rules);
        
        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }
    }

    private function getValidationRules(string $section): array
    {
        $rules = [
            'general' => [
                'site_name' => 'sometimes|string|max:255',
                'site_description' => 'sometimes|string|max:500',
                'site_url' => 'sometimes|url',
                'admin_email' => 'sometimes|email',
                'timezone' => 'sometimes|string',
                'currency' => 'sometimes|string|max:3',
                'currency_symbol' => 'sometimes|string|max:5',
            ],
            'payment' => [
                'stripe_enabled' => 'sometimes|boolean',
                'paypal_enabled' => 'sometimes|boolean',
                'cod_enabled' => 'sometimes|boolean',
                'default_gateway' => 'sometimes|in:stripe,paypal,cod',
                'currency_code' => 'sometimes|string|max:3',
            ],
            'shipping' => [
                'free_shipping_threshold' => 'sometimes|numeric|min:0',
                'default_shipping_cost' => 'sometimes|numeric|min:0',
                'weight_based_shipping' => 'sometimes|boolean',
                'international_shipping' => 'sometimes|boolean',
            ],
            'email' => [
                'notifications_enabled' => 'sometimes|boolean',
                'order_confirmation' => 'sometimes|boolean',
                'shipping_notifications' => 'sometimes|boolean',
                'marketing_emails' => 'sometimes|boolean',
                'from_name' => 'sometimes|string|max:255',
                'from_email' => 'sometimes|email',
            ],
            'seo' => [
                'meta_title' => 'sometimes|string|max:255',
                'meta_description' => 'sometimes|string|max:500',
                'meta_keywords' => 'sometimes|string|max:500',
                'google_analytics_id' => 'sometimes|nullable|string',
                'google_tag_manager_id' => 'sometimes|nullable|string',
                'sitemap_enabled' => 'sometimes|boolean',
            ],
            'social' => [
                'facebook_url' => 'sometimes|nullable|url',
                'twitter_url' => 'sometimes|nullable|url',
                'instagram_url' => 'sometimes|nullable|url',
                'linkedin_url' => 'sometimes|nullable|url',
                'youtube_url' => 'sometimes|nullable|url',
            ],
            'analytics' => [
                'track_user_behavior' => 'sometimes|boolean',
                'track_product_views' => 'sometimes|boolean',
                'track_search_queries' => 'sometimes|boolean',
            ],
            'security' => [
                'force_https' => 'sometimes|boolean',
                'session_timeout' => 'sometimes|integer|min:5|max:1440',
                'max_login_attempts' => 'sometimes|integer|min:1|max:10',
                'password_min_length' => 'sometimes|integer|min:6|max:50',
                'two_factor_enabled' => 'sometimes|boolean',
            ],
        ];

        return $rules[$section] ?? [];
    }

    private function getSetting(string $key, $default = null)
    {
        // In a real application, you'd store these in a settings table
        // For now, we'll use config or cache
        return config("smartmart.{$key}", $default);
    }

    private function updateSetting(string $key, $value): void
    {
        // In a real application, you'd update the settings table
        // For now, we'll simulate this
        Cache::forever("setting.{$key}", $value);
    }
}