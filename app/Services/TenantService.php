<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenancy\Domain;
use App\Models\Tenancy\Tenant;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class TenantService
{
    /**
     * Create a new tenant with database, domain, and initial setup.
     *
     * @param  array{name: string, email: string, plan?: string, subdomain?: string, domain?: string}  $data
     *
     * @throws RuntimeException If tenant creation fails
     */
    public function createTenant(array $data): Tenant
    {
        // Validate subdomain
        $subdomain = $data['subdomain'] ?? Str::slug($data['name']);
        $this->validateSubdomain($subdomain);

        // Note: We cannot use DB transactions here because PostgreSQL doesn't allow
        // CREATE DATABASE inside a transaction block. We'll handle rollback manually.

        $tenant = null;

        try {
            // Create tenant
            $tenant = Tenant::create([
                'name' => $data['name'],
                'plan' => $data['plan'] ?? Tenant::PLAN_TRIAL,
                'status' => Tenant::STATUS_TRIAL,
                'trial_ends_at' => now()->addDays(14),
                'data' => [
                    'admin_email' => $data['email'],
                    'created_by' => auth()->id(),
                ],
            ]);

            // Create primary domain (subdomain)
            $this->createDomain($tenant, $subdomain, isPrimary: true);

            // Create additional custom domain if provided
            if (! empty($data['domain'])) {
                $this->createDomain($tenant, $data['domain'], isPrimary: false);
            }

            // Note: Database creation, migration, and seeding are handled automatically
            // by the TenancyServiceProvider event listeners (CreateDatabase, MigrateDatabase, SeedDatabase jobs)
            // when Tenant::create() is called above

            return $tenant->fresh(['domains']);
        } catch (Exception $e) {
            // Manual rollback - clean up tenant and database if creation failed
            if ($tenant) {
                try {
                    $this->deleteTenantDatabase($tenant);
                    $tenant->domains()->delete();
                    $tenant->delete();
                } catch (Exception $cleanupError) {
                    // Log cleanup error but throw original error
                    report($cleanupError);
                }
            }

            throw new RuntimeException("Failed to create tenant: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Delete a tenant and all associated data.
     *
     * @throws RuntimeException If deletion fails
     */
    public function deleteTenant(Tenant $tenant): void
    {
        DB::beginTransaction();

        try {
            // Delete tenant database first
            $this->deleteTenantDatabase($tenant);

            // Delete domains
            $tenant->domains()->delete();

            // Delete settings
            $tenant->settings()->delete();

            // Delete tenant record
            $tenant->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new RuntimeException("Failed to delete tenant: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Suspend a tenant (disable access).
     */
    public function suspendTenant(Tenant $tenant, ?string $reason = null): void
    {
        $tenant->suspend();

        if ($reason) {
            $tenant->setCustomAttribute('suspension_reason', $reason);
        }

        $tenant->setCustomAttribute('suspended_at', now()->toIso8601String());
    }

    /**
     * Activate a tenant (enable access).
     */
    public function activateTenant(Tenant $tenant): void
    {
        $tenant->activate();
        $tenant->setCustomAttribute('suspension_reason', null);
        $tenant->setCustomAttribute('suspended_at', null);
    }

    /**
     * Update tenant plan.
     */
    public function updatePlan(Tenant $tenant, string $plan): void
    {
        if (! in_array($plan, [Tenant::PLAN_TRIAL, Tenant::PLAN_STARTER, Tenant::PLAN_PROFESSIONAL, Tenant::PLAN_ENTERPRISE])) {
            throw new RuntimeException('Invalid plan specified.');
        }

        $tenant->update([
            'plan' => $plan,
            'status' => Tenant::STATUS_ACTIVE,
        ]);
    }

    /**
     * Get tenant usage statistics.
     */
    public function getTenantUsage(Tenant $tenant): array
    {
        tenancy()->initialize($tenant);

        try {
            // Get counts from tenant database
            $users = DB::table('users')->count();
            $modules = DB::table('modules')->count();
            $records = DB::table('module_records')->count();

            // Get storage usage (in MB)
            $storageBytes = $this->calculateStorageUsage($tenant);
            $storageMB = round($storageBytes / 1024 / 1024, 2);

            return [
                'users' => $users,
                'modules' => $modules,
                'records' => $records,
                'storage_mb' => $storageMB,
                'plan' => $tenant->plan,
                'status' => $tenant->status,
                'trial_ends_at' => $tenant->trial_ends_at?->toIso8601String(),
            ];
        } catch (Exception $e) {
            return [
                'error' => 'Unable to fetch usage data',
                'message' => $e->getMessage(),
            ];
        } finally {
            tenancy()->end();
        }
    }

    /**
     * Check if subdomain is available.
     */
    public function isSubdomainAvailable(string $subdomain): bool
    {
        try {
            $this->validateSubdomain($subdomain);

            return true;
        } catch (RuntimeException) {
            return false;
        }
    }

    /**
     * Create a domain for a tenant.
     */
    private function createDomain(Tenant $tenant, string $domain, bool $isPrimary = false): Domain
    {
        $fullDomain = $this->buildFullDomain($domain);

        $domainModel = Domain::create([
            'domain' => $fullDomain,
            'tenant_id' => $tenant->id,
            'is_primary' => $isPrimary,
        ]);

        if ($isPrimary) {
            $domainModel->markAsPrimary();
        }

        return $domainModel;
    }

    /**
     * Build full domain from subdomain.
     * For subdomains: acme -> acme.vrtxcrm.local
     * For full domains: example.com -> example.com
     */
    private function buildFullDomain(string $domain): string
    {
        // If domain contains a dot, treat as full domain
        if (str_contains($domain, '.')) {
            return $domain;
        }

        // Otherwise, it's a subdomain - append central domain
        $centralDomain = config('app.domain', 'vrtxcrm.local');

        return "{$domain}.{$centralDomain}";
    }

    /**
     * Validate subdomain is available and valid.
     *
     * @throws RuntimeException If subdomain is invalid or taken
     */
    private function validateSubdomain(string $subdomain): void
    {
        // Check format
        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $subdomain)) {
            throw new RuntimeException('Subdomain must contain only lowercase letters, numbers, and hyphens.');
        }

        // Check length
        if (mb_strlen($subdomain) < 3 || mb_strlen($subdomain) > 63) {
            throw new RuntimeException('Subdomain must be between 3 and 63 characters.');
        }

        // Check reserved names
        $reserved = ['www', 'api', 'admin', 'app', 'mail', 'ftp', 'localhost', 'staging', 'dev', 'test'];
        if (in_array($subdomain, $reserved)) {
            throw new RuntimeException('This subdomain is reserved and cannot be used.');
        }

        // Check if already taken
        $fullDomain = $this->buildFullDomain($subdomain);
        if (Domain::where('domain', $fullDomain)->exists()) {
            throw new RuntimeException('This subdomain is already taken.');
        }
    }

    /**
     * Seed tenant database with initial data.
     */
    private function seedTenantDatabase(Tenant $tenant): void
    {
        tenancy()->initialize($tenant);

        try {
            // Run tenant-specific seeders
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\TenantDatabaseSeeder',
                '--force' => true,
            ]);
        } finally {
            tenancy()->end();
        }
    }

    /**
     * Delete tenant database.
     */
    private function deleteTenantDatabase(Tenant $tenant): void
    {
        try {
            Artisan::call('tenants:delete', [
                '--tenants' => [$tenant->id],
            ]);
        } catch (Exception $e) {
            // Log error but don't fail - database might not exist
            report($e);
        }
    }

    /**
     * Calculate storage usage for tenant (files + database).
     */
    private function calculateStorageUsage(Tenant $tenant): int
    {
        // This is a simplified version - in production you'd want more accurate calculations
        try {
            // Get database size
            $dbName = "tenant{$tenant->id}";
            $result = DB::selectOne('SELECT pg_database_size(?) as size', [$dbName]);
            $dbSize = $result->size ?? 0;

            // Get file storage size (would need to implement based on your storage strategy)
            $fileSize = 0; // TODO: Implement based on storage driver

            return $dbSize + $fileSize;
        } catch (Exception $e) {
            return 0;
        }
    }
}
