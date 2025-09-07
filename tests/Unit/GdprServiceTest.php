<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\DataRequest;
use App\Services\GdprService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GdprServiceTest extends TestCase
{
    use RefreshDatabase;

    private GdprService $gdprService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gdprService = app(GdprService::class);
    }

    public function test_export_user_data_includes_all_personal_information(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890'
        ]);

        $exportData = $this->gdprService->exportUserData($user);

        $this->assertArrayHasKey('personal_information', $exportData);
        $this->assertEquals('John Doe', $exportData['personal_information']['name']);
        $this->assertEquals('john@example.com', $exportData['personal_information']['email']);
        $this->assertEquals('1234567890', $exportData['personal_information']['phone']);
    }

    public function test_export_user_data_includes_orders(): void
    {
        $user = User::factory()->create();
        $orders = Order::factory()->count(2)->create(['user_id' => $user->id]);

        $exportData = $this->gdprService->exportUserData($user);

        $this->assertArrayHasKey('orders', $exportData);
        $this->assertCount(2, $exportData['orders']);
        $this->assertEquals($orders[0]->id, $exportData['orders'][0]['id']);
    }

    public function test_export_user_data_includes_consent_records(): void
    {
        $user = User::factory()->create();

        $exportData = $this->gdprService->exportUserData($user);

        $this->assertArrayHasKey('consent_records', $exportData);
        $this->assertArrayHasKey('data_requests', $exportData);
        $this->assertArrayHasKey('addresses', $exportData);
    }

    public function test_anonymize_user_data_changes_personal_information(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890'
        ]);

        $originalEmail = $user->email;
        $this->gdprService->anonymizeUserData($user);

        $user->refresh();

        $this->assertNotEquals('John Doe', $user->name);
        $this->assertNotEquals($originalEmail, $user->email);
        $this->assertStringContains('anonymized_', $user->email);
        $this->assertEquals('Anonymous User', $user->name);
        $this->assertNull($user->phone);
    }

    public function test_anonymize_user_data_preserves_order_history(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'total_amount' => 100.00
        ]);

        $this->gdprService->anonymizeUserData($user);

        $order->refresh();
        // Order should still exist but with anonymized data
        $this->assertEquals($user->id, $order->user_id);
        $this->assertEquals(100.00, $order->total_amount);
    }

    public function test_generate_export_file_creates_json_file(): void
    {
        Storage::fake('local');
        
        $user = User::factory()->create();
        $dataRequest = DataRequest::factory()->create([
            'user_id' => $user->id,
            'type' => 'export'
        ]);

        $filePath = $this->gdprService->generateExportFile($dataRequest);

        $this->assertNotNull($filePath);
        Storage::assertExists($filePath);
        
        $fileContent = Storage::get($filePath);
        $decodedContent = json_decode($fileContent, true);
        
        $this->assertIsArray($decodedContent);
        $this->assertArrayHasKey('personal_information', $decodedContent);
    }

    public function test_cleanup_expired_export_files_removes_old_files(): void
    {
        Storage::fake('local');
        
        $user = User::factory()->create();
        
        // Create an expired data request with export file
        $expiredRequest = DataRequest::factory()->create([
            'user_id' => $user->id,
            'type' => 'export',
            'status' => 'completed',
            'expires_at' => now()->subDays(1),
            'export_file_path' => 'exports/expired-export.json'
        ]);

        // Create the file
        Storage::put('exports/expired-export.json', json_encode(['test' => 'data']));

        $this->gdprService->cleanupExpiredExportFiles();

        $expiredRequest->refresh();
        
        $this->assertEquals('expired', $expiredRequest->status);
        $this->assertNull($expiredRequest->export_file_path);
        Storage::assertMissing('exports/expired-export.json');
    }

    public function test_get_overdue_requests_returns_old_pending_requests(): void
    {
        $user = User::factory()->create();
        
        // Create an overdue request (created more than 30 days ago)
        $overdueRequest = DataRequest::factory()->create([
            'user_id' => $user->id,
            'type' => 'export',
            'status' => 'pending',
            'created_at' => now()->subDays(35)
        ]);

        // Create a recent request
        $recentRequest = DataRequest::factory()->create([
            'user_id' => $user->id,
            'type' => 'export',
            'status' => 'pending',
            'created_at' => now()->subDays(5)
        ]);

        $overdueRequests = $this->gdprService->getOverdueRequests();

        $this->assertTrue($overdueRequests->contains($overdueRequest));
        $this->assertFalse($overdueRequests->contains($recentRequest));
    }

    public function test_delete_user_data_removes_associated_records(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        
        // Create associated data
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->gdprService->deleteUserData($user);

        // User should be soft deleted
        $this->assertSoftDeleted('users', ['id' => $user->id]);
        
        // Orders should be anonymized but not deleted (for accounting purposes)
        $order->refresh();
        $this->assertEquals($user->id, $order->user_id);
    }

    public function test_export_data_excludes_sensitive_fields(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('secret'),
            'remember_token' => 'token123'
        ]);

        $exportData = $this->gdprService->exportUserData($user);

        $this->assertArrayNotHasKey('password', $exportData['personal_information']);
        $this->assertArrayNotHasKey('remember_token', $exportData['personal_information']);
    }

    public function test_export_data_includes_formatted_dates(): void
    {
        $user = User::factory()->create();

        $exportData = $this->gdprService->exportUserData($user);

        $this->assertArrayHasKey('account_created', $exportData['personal_information']);
        $this->assertArrayHasKey('last_updated', $exportData['personal_information']);
    }

    public function test_export_data_includes_metadata(): void
    {
        $user = User::factory()->create();

        $exportData = $this->gdprService->exportUserData($user);

        $this->assertArrayHasKey('export_metadata', $exportData);
        $this->assertArrayHasKey('generated_at', $exportData['export_metadata']);
        $this->assertArrayHasKey('user_id', $exportData['export_metadata']);
        $this->assertArrayHasKey('format_version', $exportData['export_metadata']);
    }

    public function test_anonymization_is_irreversible(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        $originalName = $user->name;
        $originalEmail = $user->email;

        $this->gdprService->anonymizeUserData($user);
        $user->refresh();

        // Data should be permanently changed
        $this->assertNotEquals($originalName, $user->name);
        $this->assertNotEquals($originalEmail, $user->email);
        
        // Should not be able to recover original data
        $this->assertStringNotContainsString('John', $user->name);
        $this->assertStringNotContainsString('john@example.com', $user->email);
    }
}