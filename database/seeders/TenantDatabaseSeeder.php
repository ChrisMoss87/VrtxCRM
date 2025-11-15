<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class TenantDatabaseSeeder extends Seeder
{
    /**
     * Seed the tenant's database.
     * This runs INSIDE the tenant context after tenant DB is created.
     */
    public function run(): void
    {
        // Get tenant data
        $tenantData = tenant('data');
        $adminEmail = $tenantData['admin_email'] ?? 'admin@test.com';

        $this->command->info('Tenant data: '.json_encode($tenantData));
        $this->command->info("Using admin email: {$adminEmail}");

        // Create default admin user for tenant
        \App\Models\User::create([
            'name' => 'Admin User',
            'email' => $adminEmail,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $this->command->info('✓ Created admin user for tenant: '.tenant('name'));

        // Seed CRM modules
        $this->call(ModuleSeeder::class);
        $this->command->info('✓ Seeded CRM modules for tenant: '.tenant('name'));

        // Seed sample contacts
        $this->call(SampleContactsSeeder::class);
    }
}
