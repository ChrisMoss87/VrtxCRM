<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Services\TenantService;
use Illuminate\Database\Seeder;

final class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates sample tenants for development/testing.
     */
    public function run(): void
    {
        $tenantService = app(TenantService::class);

        $tenants = [
            [
                'name' => 'Acme Corporation',
                'email' => 'admin@acme.com',
                'subdomain' => 'acme',
                'plan' => 'professional',
                'status' => 'active',
            ],
            [
                'name' => 'Startup Inc',
                'email' => 'founder@startup.com',
                'subdomain' => 'startup',
                'plan' => 'trial',
                'status' => 'trial',
            ],
            [
                'name' => 'Enterprise Co',
                'email' => 'it@enterprise.com',
                'subdomain' => 'enterprise',
                'plan' => 'enterprise',
                'status' => 'active',
            ],
            [
                'name' => 'Demo Company',
                'email' => 'demo@demo.com',
                'subdomain' => 'demo',
                'plan' => 'starter',
                'status' => 'active',
            ],
        ];

        foreach ($tenants as $tenantData) {
            try {
                $tenant = $tenantService->createTenant([
                    'name' => $tenantData['name'],
                    'email' => $tenantData['email'],
                    'subdomain' => $tenantData['subdomain'],
                    'plan' => $tenantData['plan'],
                    'seed' => true, // Seed tenant database
                ]);

                // Update status if not trial
                if ($tenantData['status'] !== 'trial') {
                    $tenant->update(['status' => $tenantData['status']]);
                }

                $this->command->info("✓ Created tenant: {$tenantData['name']} ({$tenantData['subdomain']})");
            } catch (\Exception $e) {
                $this->command->error("✗ Failed to create tenant {$tenantData['name']}: {$e->getMessage()}");
            }
        }

        $this->command->info("\n✓ Tenant seeding completed!");
        $this->command->info("Access tenants at:");
        foreach ($tenants as $t) {
            $this->command->line("  • http://{$t['subdomain']}.vrtxcrm.local");
        }
    }
}
