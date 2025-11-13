<?php

declare(strict_types=1);

namespace Tests\Feature\Tenancy;

use App\Models\Tenancy\Tenant;
use App\Services\TenantService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

final class TenantCreationTest extends TestCase
{
    // Note: We don't use RefreshDatabase because PostgreSQL doesn't allow
    // CREATE DATABASE inside a transaction block. We manually clean up instead.

    private TenantService $tenantService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenantService = app(TenantService::class);

        // Run migrations on landlord database
        $this->artisan('migrate:fresh')->assertExitCode(0);
    }

    protected function tearDown(): void
    {
        // Clean up any tenant databases created during tests
        $tenants = Tenant::all();
        foreach ($tenants as $tenant) {
            try {
                $this->tenantService->deleteTenant($tenant);
            } catch (Exception $e) {
                // Ignore errors during cleanup
            }
        }

        parent::tearDown();
    }

    public function test_can_create_tenant_with_subdomain(): void
    {
        $data = [
            'name' => 'Test Company',
            'email' => 'admin@test.com',
            'subdomain' => 'testcompany',
            'seed' => false, // Skip seeding for faster tests
        ];

        $tenant = $this->tenantService->createTenant($data);

        $this->assertInstanceOf(Tenant::class, $tenant);
        $this->assertEquals('Test Company', $tenant->name);
        $this->assertEquals('trial', $tenant->status);
        $this->assertEquals('trial', $tenant->plan);
        $this->assertNotNull($tenant->trial_ends_at);
        $this->assertEquals('admin@test.com', $tenant->data['admin_email']);
    }

    public function test_tenant_creation_creates_primary_domain(): void
    {
        $data = [
            'name' => 'Domain Test',
            'email' => 'admin@domaintest.com',
            'subdomain' => 'domaintest',
            'seed' => false,
        ];

        $tenant = $this->tenantService->createTenant($data);

        $this->assertCount(1, $tenant->domains);

        $domain = $tenant->domains->first();
        $this->assertEquals('domaintest.vrtxcrm.local', $domain->domain);
        $this->assertTrue($domain->is_primary);
    }

    public function test_tenant_creation_creates_separate_database(): void
    {
        $data = [
            'name' => 'Database Test',
            'email' => 'admin@dbtest.com',
            'subdomain' => 'dbtest',
            'seed' => false,
        ];

        $tenant = $this->tenantService->createTenant($data);

        // Check that tenant database exists
        $dbName = "tenant{$tenant->id}";

        // Query to check if database exists
        $exists = DB::selectOne('SELECT 1 FROM pg_database WHERE datname = ?', [$dbName]);

        $this->assertNotNull($exists, "Tenant database {$dbName} should exist");
    }

    public function test_tenant_creation_with_custom_domain(): void
    {
        $data = [
            'name' => 'Custom Domain Test',
            'email' => 'admin@custom.com',
            'subdomain' => 'customtest',
            'domain' => 'custom-domain.com',
            'seed' => false,
        ];

        $tenant = $this->tenantService->createTenant($data);

        $this->assertCount(2, $tenant->domains);

        $primaryDomain = $tenant->domains->where('is_primary', true)->first();
        $this->assertEquals('customtest.vrtxcrm.local', $primaryDomain->domain);

        $customDomain = $tenant->domains->where('is_primary', false)->first();
        $this->assertEquals('custom-domain.com', $customDomain->domain);
    }

    public function test_cannot_create_tenant_with_duplicate_subdomain(): void
    {
        // Create first tenant
        $this->tenantService->createTenant([
            'name' => 'First Tenant',
            'email' => 'first@test.com',
            'subdomain' => 'duplicate',
            'seed' => false,
        ]);

        // Attempt to create second tenant with same subdomain
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('This subdomain is already taken');

        $this->tenantService->createTenant([
            'name' => 'Second Tenant',
            'email' => 'second@test.com',
            'subdomain' => 'duplicate',
            'seed' => false,
        ]);
    }

    public function test_cannot_create_tenant_with_reserved_subdomain(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('This subdomain is reserved');

        $this->tenantService->createTenant([
            'name' => 'Reserved Test',
            'email' => 'admin@test.com',
            'subdomain' => 'admin', // Reserved
            'seed' => false,
        ]);
    }

    public function test_cannot_create_tenant_with_invalid_subdomain_format(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Subdomain must contain only lowercase letters');

        $this->tenantService->createTenant([
            'name' => 'Invalid Format',
            'email' => 'admin@test.com',
            'subdomain' => 'Invalid_Subdomain!', // Invalid characters
            'seed' => false,
        ]);
    }

    public function test_can_delete_tenant_and_database(): void
    {
        $tenant = $this->tenantService->createTenant([
            'name' => 'Delete Test',
            'email' => 'admin@delete.com',
            'subdomain' => 'deletetest',
            'seed' => false,
        ]);

        $tenantId = $tenant->id;
        $dbName = "tenant{$tenantId}";

        $this->tenantService->deleteTenant($tenant);

        // Check tenant is deleted
        $this->assertDatabaseMissing('tenants', ['id' => $tenantId]);

        // Check domains are deleted
        $this->assertDatabaseMissing('domains', ['tenant_id' => $tenantId]);

        // Check database is deleted
        $exists = DB::selectOne('SELECT 1 FROM pg_database WHERE datname = ?', [$dbName]);
        $this->assertNull($exists, "Tenant database {$dbName} should be deleted");
    }

    public function test_can_suspend_and_activate_tenant(): void
    {
        $tenant = $this->tenantService->createTenant([
            'name' => 'Suspend Test',
            'email' => 'admin@suspend.com',
            'subdomain' => 'suspendtest',
            'seed' => false,
        ]);

        // Suspend tenant
        $this->tenantService->suspendTenant($tenant, 'Payment overdue');

        $tenant->refresh();
        $this->assertTrue($tenant->isSuspended());
        $this->assertEquals('Payment overdue', $tenant->getCustomAttribute('suspension_reason'));

        // Activate tenant
        $this->tenantService->activateTenant($tenant);

        $tenant->refresh();
        $this->assertTrue($tenant->isActive());
        $this->assertNull($tenant->getCustomAttribute('suspension_reason'));
    }

    public function test_can_update_tenant_plan(): void
    {
        $tenant = $this->tenantService->createTenant([
            'name' => 'Plan Test',
            'email' => 'admin@plan.com',
            'subdomain' => 'plantest',
            'seed' => false,
        ]);

        $this->assertEquals('trial', $tenant->plan);

        // Upgrade to professional
        $this->tenantService->updatePlan($tenant, 'professional');

        $tenant->refresh();
        $this->assertEquals('professional', $tenant->plan);
        $this->assertEquals('active', $tenant->status);
    }

    public function test_subdomain_availability_check(): void
    {
        $this->assertTrue($this->tenantService->isSubdomainAvailable('available'));

        $this->tenantService->createTenant([
            'name' => 'Availability Test',
            'email' => 'admin@avail.com',
            'subdomain' => 'taken',
            'seed' => false,
        ]);

        $this->assertFalse($this->tenantService->isSubdomainAvailable('taken'));
        $this->assertFalse($this->tenantService->isSubdomainAvailable('admin')); // Reserved
    }
}
