<?php

declare(strict_types=1);

namespace Tests\Feature\Tenancy;

use App\Models\Tenancy\Tenant;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class TenantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('ROLLBACK');
        $this->beginDatabaseTransaction();

        try {
            $existingTenants = Tenant::all();
            foreach ($existingTenants as $tenant) {
                $tenant->delete();
            }
        } catch (Exception $e) {
            //
        }
    }

    protected function tearDown(): void
    {
        try {
            $tenants = Tenant::all();
            foreach ($tenants as $tenant) {
                $tenant->delete();
            }
        } catch (Exception $e) {
            //
        }

        parent::tearDown();
    }

    public function test_can_create_tenant_with_domain(): void
    {
        $tenant = Tenant::create([
            'id' => 'test-company',
            'name' => 'Test Company',
        ]);

        $domain = $tenant->domains()->create([
            'domain' => 'test.localhost',
        ]);

        $this->assertDatabaseHas('tenants', [
            'id' => 'test-company',
            'name' => 'Test Company',
        ]);

        $this->assertDatabaseHas('domains', [
            'domain' => 'test.localhost',
            'tenant_id' => 'test-company',
        ]);
    }

    public function test_tenant_has_trial_status_by_default(): void
    {
        $tenant = Tenant::create([
            'id' => 'trial-tenant',
            'name' => 'Trial Tenant',
        ]);

        $this->assertEquals('trial', $tenant->plan);
        $this->assertEquals('trial', $tenant->status);
    }

    public function test_tenant_is_on_trial_when_trial_not_expired(): void
    {
        $tenant = Tenant::create([
            'id' => 'active-trial',
            'name' => 'Active Trial',
            'status' => 'trial',
            'trial_ends_at' => Carbon::now()->addDays(7),
        ]);

        $this->assertTrue($tenant->isOnTrial());
        $this->assertFalse($tenant->trialHasExpired());
    }

    public function test_tenant_trial_has_expired_when_date_is_past(): void
    {
        $tenant = Tenant::create([
            'id' => 'expired-trial',
            'name' => 'Expired Trial',
            'status' => 'trial',
            'trial_ends_at' => Carbon::now()->subDays(1),
        ]);

        $this->assertFalse($tenant->isOnTrial());
        $this->assertTrue($tenant->trialHasExpired());
    }

    public function test_tenant_is_active_when_status_is_active(): void
    {
        $tenant = Tenant::create([
            'id' => 'active-tenant',
            'name' => 'Active Tenant',
            'status' => 'active',
        ]);

        $this->assertTrue($tenant->isActive());
        $this->assertFalse($tenant->isSuspended());
    }

    public function test_tenant_is_suspended_when_status_is_suspended(): void
    {
        $tenant = Tenant::create([
            'id' => 'suspended-tenant',
            'name' => 'Suspended Tenant',
            'status' => 'suspended',
        ]);

        $this->assertTrue($tenant->isSuspended());
        $this->assertFalse($tenant->isActive());
    }

    public function test_tenant_is_suspended_when_past_due(): void
    {
        $tenant = Tenant::create([
            'id' => 'past-due-tenant',
            'name' => 'Past Due Tenant',
            'status' => 'past_due',
        ]);

        $this->assertTrue($tenant->isSuspended());
    }

    public function test_can_activate_tenant(): void
    {
        $tenant = Tenant::create([
            'id' => 'inactive-tenant',
            'name' => 'Inactive Tenant',
            'status' => 'suspended',
        ]);

        $tenant->activate();

        $this->assertTrue($tenant->isActive());
        $this->assertEquals('active', $tenant->fresh()->status);
    }

    public function test_can_suspend_tenant(): void
    {
        $tenant = Tenant::create([
            'id' => 'active-tenant',
            'name' => 'Active Tenant',
            'status' => 'active',
        ]);

        $tenant->suspend();

        $this->assertTrue($tenant->isSuspended());
        $this->assertEquals('suspended', $tenant->fresh()->status);
    }

    public function test_tenant_can_have_multiple_domains(): void
    {
        $tenant = Tenant::create([
            'id' => 'multi-domain',
            'name' => 'Multi Domain Tenant',
        ]);

        $tenant->domains()->create(['domain' => 'primary.localhost']);
        $tenant->domains()->create(['domain' => 'secondary.localhost']);
        $tenant->domains()->create(['domain' => 'www.primary.localhost']);

        $this->assertCount(3, $tenant->domains);
        $this->assertDatabaseHas('domains', ['domain' => 'primary.localhost']);
        $this->assertDatabaseHas('domains', ['domain' => 'secondary.localhost']);
        $this->assertDatabaseHas('domains', ['domain' => 'www.primary.localhost']);
    }

    public function test_tenant_stores_stripe_customer_data(): void
    {
        $tenant = Tenant::create([
            'id' => 'stripe-tenant',
            'name' => 'Stripe Tenant',
            'stripe_customer_id' => 'cus_123abc',
            'stripe_subscription_id' => 'sub_456def',
        ]);

        $this->assertEquals('cus_123abc', $tenant->stripe_customer_id);
        $this->assertEquals('sub_456def', $tenant->stripe_subscription_id);
    }

    public function test_tenant_tracks_subscription_dates(): void
    {
        $trialEndsAt = Carbon::now()->addDays(14);
        $subscriptionEndsAt = Carbon::now()->addYear();

        $tenant = Tenant::create([
            'id' => 'dated-tenant',
            'name' => 'Dated Tenant',
            'trial_ends_at' => $trialEndsAt,
            'subscription_ends_at' => $subscriptionEndsAt,
        ]);

        $this->assertNotNull($tenant->trial_ends_at);
        $this->assertNotNull($tenant->subscription_ends_at);
        $this->assertEquals($trialEndsAt->toDateString(), $tenant->trial_ends_at->toDateString());
        $this->assertEquals($subscriptionEndsAt->toDateString(), $tenant->subscription_ends_at->toDateString());
    }

    protected function beginDatabaseTransaction()
    {
        //
    }
}
