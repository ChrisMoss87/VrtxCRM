<?php

declare(strict_types=1);

namespace Tests\Feature\Tenancy;

use App\Infrastructure\Persistence\Eloquent\Models\ModuleModel;
use App\Models\Tenancy\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class TenantIsolationTest extends TestCase
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
        } catch (\Exception $e) {
            //
        }

        $this->artisan('tenants:migrate');
    }

    protected function beginDatabaseTransaction()
    {
        //
    }

    protected function tearDown(): void
    {
        try {
            $tenants = Tenant::all();
            foreach ($tenants as $tenant) {
                $tenant->delete();
            }
        } catch (\Exception $e) {
            //
        }

        parent::tearDown();
    }

    public function test_tenant_has_separate_database(): void
    {
        $tenant1 = Tenant::create(['id' => 'tenant-1', 'name' => 'Tenant 1']);
        $tenant2 = Tenant::create(['id' => 'tenant-2', 'name' => 'Tenant 2']);

        $tenant1->run(function () {
            $this->assertNotNull(DB::connection()->getDatabaseName());
        });

        $tenant2->run(function () {
            $this->assertNotNull(DB::connection()->getDatabaseName());
        });

        $tenant1DatabaseName = null;
        $tenant2DatabaseName = null;

        $tenant1->run(function () use (&$tenant1DatabaseName) {
            $tenant1DatabaseName = DB::connection()->getDatabaseName();
        });

        $tenant2->run(function () use (&$tenant2DatabaseName) {
            $tenant2DatabaseName = DB::connection()->getDatabaseName();
        });

        $this->assertNotEquals($tenant1DatabaseName, $tenant2DatabaseName);
    }

    public function test_data_is_isolated_between_tenants(): void
    {
        $tenant1 = Tenant::create(['id' => 'company-a', 'name' => 'Company A']);
        $tenant2 = Tenant::create(['id' => 'company-b', 'name' => 'Company B']);

        $tenant1->run(function () {
            User::create([
                'name' => 'User from Tenant 1',
                'email' => 'user1@tenant1.com',
                'password' => bcrypt('password'),
            ]);
        });

        $tenant2->run(function () {
            User::create([
                'name' => 'User from Tenant 2',
                'email' => 'user2@tenant2.com',
                'password' => bcrypt('password'),
            ]);
        });

        $tenant1->run(function () {
            $this->assertCount(1, User::all());
            $this->assertEquals('user1@tenant1.com', User::first()->email);
        });

        $tenant2->run(function () {
            $this->assertCount(1, User::all());
            $this->assertEquals('user2@tenant2.com', User::first()->email);
        });
    }

    public function test_modules_are_isolated_between_tenants(): void
    {
        $tenant1 = Tenant::create(['id' => 'crm-a', 'name' => 'CRM A']);
        $tenant2 = Tenant::create(['id' => 'crm-b', 'name' => 'CRM B']);

        $tenant1->run(function () {
            $initialCount = ModuleModel::count();

            ModuleModel::create([
                'name' => 'Custom Module for Tenant 1',
                'singular_name' => 'Custom Module',
                'is_active' => true,
            ]);

            $this->assertCount($initialCount + 1, ModuleModel::all());
            $this->assertTrue(ModuleModel::where('name', 'Custom Module for Tenant 1')->exists());
        });

        $tenant2->run(function () {
            $initialCount = ModuleModel::count();

            ModuleModel::create([
                'name' => 'Custom Module for Tenant 2',
                'singular_name' => 'Custom Module',
                'is_active' => true,
            ]);

            $this->assertCount($initialCount + 1, ModuleModel::all());
            $this->assertTrue(ModuleModel::where('name', 'Custom Module for Tenant 2')->exists());
            $this->assertFalse(ModuleModel::where('name', 'Custom Module for Tenant 1')->exists());
        });
    }

    public function test_cannot_access_another_tenant_data(): void
    {
        $tenant1 = Tenant::create(['id' => 'secure-a', 'name' => 'Secure A']);
        $tenant2 = Tenant::create(['id' => 'secure-b', 'name' => 'Secure B']);

        $userId = null;

        $tenant1->run(function () use (&$userId) {
            $user = User::create([
                'name' => 'Tenant 1 User',
                'email' => 'secure@tenant1.com',
                'password' => bcrypt('password'),
            ]);
            $userId = $user->id;
        });

        $tenant2->run(function () use ($userId) {
            $this->assertNull(User::find($userId));
        });
    }

    public function test_database_connection_switches_correctly(): void
    {
        $tenant = Tenant::create(['id' => 'switch-test', 'name' => 'Switch Test']);

        $centralDb = DB::connection()->getDatabaseName();

        $tenant->run(function () use ($centralDb) {
            $tenantDb = DB::connection()->getDatabaseName();
            $this->assertNotEquals($centralDb, $tenantDb);
            $this->assertStringContainsString('tenant', $tenantDb);
        });

        $afterDb = DB::connection()->getDatabaseName();
        $this->assertEquals($centralDb, $afterDb);
    }
}
