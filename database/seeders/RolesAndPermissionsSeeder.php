<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User management
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            
            // Product management
            'products.view',
            'products.create',
            'products.update',
            'products.delete',
            'products.publish',
            
            // Category management
            'categories.view',
            'categories.create',
            'categories.update',
            'categories.delete',
            
            // Order management
            'orders.view',
            'orders.create',
            'orders.update',
            'orders.delete',
            'orders.fulfill',
            'orders.refund',
            
            // Review management
            'reviews.view',
            'reviews.create',
            'reviews.update',
            'reviews.delete',
            'reviews.moderate',
            
            // Subscription management
            'subscriptions.view',
            'subscriptions.create',
            'subscriptions.update',
            'subscriptions.delete',
            'subscriptions.manage',
            
            // Analytics and reports
            'analytics.view',
            'reports.view',
            'reports.export',
            
            // Admin panel
            'admin.access',
            'admin.settings',
            'admin.system',
            
            // Cart management
            'cart.manage',
            
            // Payment management
            'payments.view',
            'payments.process',
            'payments.refund',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $sellerRole = Role::create(['name' => 'seller']);
        $sellerRole->givePermissionTo([
            'products.view',
            'products.create', 
            'products.update',
            'products.delete',
            'products.publish',
            'orders.view',
            'orders.update',
            'orders.fulfill',
            'analytics.view',
            'reviews.view',
            'subscriptions.view',
            'subscriptions.create',
            'subscriptions.update',
            'subscriptions.manage',
        ]);

        $buyerRole = Role::create(['name' => 'buyer']);
        $buyerRole->givePermissionTo([
            'products.view',
            'orders.view',
            'orders.create',
            'reviews.view',
            'reviews.create',
            'reviews.update',
            'cart.manage',
            'subscriptions.view',
            'subscriptions.create',
            'subscriptions.update',
        ]);

        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@smartmart.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        // Create seller user
        $seller = User::create([
            'name' => 'Seller User',
            'email' => 'seller@smartmart.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $seller->assignRole('seller');

        // Create buyer user
        $buyer = User::create([
            'name' => 'Buyer User',
            'email' => 'buyer@smartmart.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $buyer->assignRole('buyer');

        $this->command->info('Roles and permissions created successfully!');
        $this->command->info('Admin: admin@smartmart.com / password');
        $this->command->info('Seller: seller@smartmart.com / password');
        $this->command->info('Buyer: buyer@smartmart.com / password');
    }
}