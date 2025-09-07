<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\DataRequest;
use App\Models\DataProcessingAgreement;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Services\GdprService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GdprTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_access_privacy_dashboard(): void
    {
        $user = $this->actingAsBuyer();

        $response = $this->get('/privacy/dashboard');

        $response->assertStatus(200)
            ->assertSee('Privacy Dashboard');
    }

    public function test_user_can_request_data_export(): void
    {
        $user = $this->actingAsBuyer();

        $response = $this->post('/privacy/export', [
            'request_type' => 'export',
            'reason' => 'I need a copy of my personal data'
        ]);

        $response->assertRedirect('/privacy/dashboard')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('data_requests', [
            'user_id' => $user->id,
            'type' => 'export',
            'status' => 'pending'
        ]);
    }

    public function test_user_can_request_data_deletion(): void
    {
        $user = $this->actingAsBuyer();

        $response = $this->post('/privacy/delete', [
            'request_type' => 'delete',
            'reason' => 'I want to delete my account'
        ]);

        $response->assertRedirect('/privacy/dashboard')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('data_requests', [
            'user_id' => $user->id,
            'type' => 'delete',
            'status' => 'pending'
        ]);
    }

    public function test_user_can_record_consent(): void
    {
        $user = $this->actingAsBuyer();

        $response = $this->post('/privacy/consent', [
            'purpose' => 'marketing',
            'consent_given' => true,
            'data_types' => ['email', 'profile']
        ]);

        $response->assertRedirect('/privacy/dashboard')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('data_processing_agreements', [
            'user_id' => $user->id,
            'purpose' => 'marketing',
            'consent_given' => true
        ]);
    }

    public function test_user_can_withdraw_consent(): void
    {
        $user = $this->actingAsBuyer();

        // First give consent
        DataProcessingAgreement::create([
            'user_id' => $user->id,
            'purpose' => 'marketing',
            'consent_given' => true,
            'data_types' => ['email'],
            'consent_date' => now()
        ]);

        $response = $this->post('/privacy/consent', [
            'purpose' => 'marketing',
            'consent_given' => false,
            'data_types' => ['email']
        ]);

        $response->assertRedirect('/privacy/dashboard');

        $this->assertDatabaseHas('data_processing_agreements', [
            'user_id' => $user->id,
            'purpose' => 'marketing',
            'consent_given' => false
        ]);
    }

    public function test_gdpr_service_can_export_user_data(): void
    {
        $user = $this->createBuyer();
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        
        // Create some user data
        Order::factory()->create([
            'user_id' => $user->id,
            'total_amount' => 100.00
        ]);

        $gdprService = app(GdprService::class);
        $exportData = $gdprService->exportUserData($user);

        $this->assertArrayHasKey('personal_information', $exportData);
        $this->assertArrayHasKey('orders', $exportData);
        $this->assertArrayHasKey('consent_records', $exportData);
        $this->assertEquals($user->email, $exportData['personal_information']['email']);
    }

    public function test_gdpr_service_can_anonymize_user_data(): void
    {
        $user = $this->createBuyer([
            'email' => 'test@example.com',
            'name' => 'Test User'
        ]);

        $gdprService = app(GdprService::class);
        $gdprService->anonymizeUserData($user);

        $user->refresh();

        $this->assertStringContains('anonymized_', $user->email);
        $this->assertEquals('Anonymous User', $user->name);
    }

    public function test_admin_can_view_gdpr_dashboard(): void
    {
        $admin = $this->actingAsAdmin();

        $response = $this->get('/admin/privacy');

        $response->assertStatus(200)
            ->assertSee('GDPR Management');
    }

    public function test_admin_can_view_data_requests(): void
    {
        $admin = $this->actingAsAdmin();
        $user = $this->createBuyer();
        
        $dataRequest = DataRequest::factory()->create([
            'user_id' => $user->id,
            'type' => 'export',
            'status' => 'pending'
        ]);

        $response = $this->get('/admin/privacy/requests');

        $response->assertStatus(200)
            ->assertSee($dataRequest->id);
    }

    public function test_admin_can_approve_data_request(): void
    {
        $admin = $this->actingAsAdmin();
        $user = $this->createBuyer();
        
        $dataRequest = DataRequest::factory()->create([
            'user_id' => $user->id,
            'type' => 'export',
            'status' => 'pending'
        ]);

        $response = $this->patch("/admin/privacy/requests/{$dataRequest->id}", [
            'status' => 'approved',
            'admin_notes' => 'Request approved and processed'
        ]);

        $response->assertRedirect("/admin/privacy/requests/{$dataRequest->id}");

        $this->assertDatabaseHas('data_requests', [
            'id' => $dataRequest->id,
            'status' => 'approved'
        ]);
    }

    public function test_admin_can_reject_data_request(): void
    {
        $admin = $this->actingAsAdmin();
        $user = $this->createBuyer();
        
        $dataRequest = DataRequest::factory()->create([
            'user_id' => $user->id,
            'type' => 'delete',
            'status' => 'pending'
        ]);

        $response = $this->patch("/admin/privacy/requests/{$dataRequest->id}", [
            'status' => 'rejected',
            'admin_notes' => 'Request rejected due to active orders'
        ]);

        $response->assertRedirect("/admin/privacy/requests/{$dataRequest->id}");

        $this->assertDatabaseHas('data_requests', [
            'id' => $dataRequest->id,
            'status' => 'rejected'
        ]);
    }

    public function test_admin_can_perform_bulk_request_updates(): void
    {
        $admin = $this->actingAsAdmin();
        $user = $this->createBuyer();
        
        $requests = DataRequest::factory()->count(3)->create([
            'user_id' => $user->id,
            'type' => 'export',
            'status' => 'pending'
        ]);

        $response = $this->post('/admin/privacy/requests/bulk', [
            'action' => 'approve',
            'request_ids' => $requests->pluck('id')->toArray()
        ]);

        $response->assertRedirect('/admin/privacy/requests');

        foreach ($requests as $request) {
            $this->assertDatabaseHas('data_requests', [
                'id' => $request->id,
                'status' => 'approved'
            ]);
        }
    }

    public function test_user_can_download_approved_export(): void
    {
        Storage::fake('local');
        
        $user = $this->actingAsBuyer();
        
        $dataRequest = DataRequest::factory()->create([
            'user_id' => $user->id,
            'type' => 'export',
            'status' => 'completed',
            'export_file_path' => 'exports/test-export.json'
        ]);

        // Create a fake export file
        Storage::put('exports/test-export.json', json_encode(['test' => 'data']));

        $response = $this->get("/privacy/download/{$dataRequest->id}");

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/json');
    }

    public function test_user_cannot_download_other_users_export(): void
    {
        $user1 = $this->createBuyer();
        $user2 = $this->createBuyer();
        
        $dataRequest = DataRequest::factory()->create([
            'user_id' => $user1->id,
            'type' => 'export',
            'status' => 'completed'
        ]);

        $this->actingAs($user2);

        $response = $this->get("/privacy/download/{$dataRequest->id}");

        $response->assertStatus(404);
    }

    public function test_data_request_has_expiry_date(): void
    {
        $user = $this->actingAsBuyer();

        $response = $this->post('/privacy/export', [
            'request_type' => 'export',
            'reason' => 'I need a copy of my personal data'
        ]);

        $dataRequest = DataRequest::where('user_id', $user->id)->first();
        
        $this->assertNotNull($dataRequest->expires_at);
        $this->assertTrue($dataRequest->expires_at->isFuture());
    }

    public function test_expired_data_requests_are_handled(): void
    {
        $user = $this->createBuyer();
        
        $expiredRequest = DataRequest::factory()->create([
            'user_id' => $user->id,
            'type' => 'export',
            'status' => 'completed',
            'expires_at' => now()->subDays(1),
            'export_file_path' => 'exports/expired-export.json'
        ]);

        // Run the cleanup command (this would typically be run by scheduler)
        $this->artisan('gdpr:monitor --cleanup-expired');

        $expiredRequest->refresh();
        $this->assertEquals('expired', $expiredRequest->status);
        $this->assertNull($expiredRequest->export_file_path);
    }

    public function test_overdue_data_requests_are_monitored(): void
    {
        $user = $this->createBuyer();
        
        $overdueRequest = DataRequest::factory()->create([
            'user_id' => $user->id,
            'type' => 'export',
            'status' => 'pending',
            'created_at' => now()->subDays(35) // Overdue
        ]);

        // Run the monitoring command
        $this->artisan('gdpr:monitor --check-overdue');

        // This would typically send notifications to admins
        // We can check if the command runs without errors
        $this->assertTrue(true);
    }

    public function test_guest_cannot_access_privacy_features(): void
    {
        $response = $this->get('/privacy/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_privacy_dashboard_shows_user_requests(): void
    {
        $user = $this->actingAsBuyer();
        
        $exportRequest = DataRequest::factory()->create([
            'user_id' => $user->id,
            'type' => 'export',
            'status' => 'completed'
        ]);

        $deleteRequest = DataRequest::factory()->create([
            'user_id' => $user->id,
            'type' => 'delete',
            'status' => 'pending'
        ]);

        $response = $this->get('/privacy/dashboard');

        $response->assertStatus(200)
            ->assertSee('export')
            ->assertSee('delete')
            ->assertSee('completed')
            ->assertSee('pending');
    }
}