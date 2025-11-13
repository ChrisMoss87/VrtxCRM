<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Models\ModuleModel;
use App\Infrastructure\Persistence\Eloquent\Models\ModuleRecordModel;
use Illuminate\Database\Seeder;

final class SampleContactsSeeder extends Seeder
{
    /**
     * Seed sample contact records for testing.
     */
    public function run(): void
    {
        // Get the Contacts module
        $contactsModule = ModuleModel::where('api_name', 'contacts')->first();

        if (! $contactsModule) {
            $this->command->error('Contacts module not found. Run ModuleSeeder first.');

            return;
        }

        $contacts = [
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@example.com',
                'phone' => '+1 (555) 123-4567',
                'job_title' => 'Senior Developer',
                'status' => 'active',
                'street' => '123 Main St',
                'city' => 'San Francisco',
                'state' => 'CA',
                'postal_code' => '94102',
                'country' => 'United States',
                'notes' => 'Great technical skills. Interested in enterprise solutions.',
            ],
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane.smith@techcorp.com',
                'phone' => '+1 (555) 234-5678',
                'job_title' => 'CTO',
                'status' => 'active',
                'street' => '456 Oak Ave',
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => '10001',
                'country' => 'United States',
                'notes' => 'Decision maker. Looking for CRM solutions.',
            ],
            [
                'first_name' => 'Bob',
                'last_name' => 'Johnson',
                'email' => 'bob.johnson@startup.io',
                'phone' => '+1 (555) 345-6789',
                'job_title' => 'Product Manager',
                'status' => 'active',
                'street' => '789 Pine Rd',
                'city' => 'Austin',
                'state' => 'TX',
                'postal_code' => '78701',
                'country' => 'United States',
                'notes' => 'Startup founder. Needs scalable solution.',
            ],
            [
                'first_name' => 'Alice',
                'last_name' => 'Williams',
                'email' => 'alice.williams@enterprise.com',
                'phone' => '+1 (555) 456-7890',
                'job_title' => 'VP of Sales',
                'status' => 'active',
                'street' => '321 Elm St',
                'city' => 'Chicago',
                'state' => 'IL',
                'postal_code' => '60601',
                'country' => 'United States',
                'notes' => 'Enterprise client. Requires custom integrations.',
            ],
            [
                'first_name' => 'Charlie',
                'last_name' => 'Brown',
                'email' => 'charlie.brown@consulting.com',
                'phone' => '+1 (555) 567-8901',
                'job_title' => 'Consultant',
                'status' => 'inactive',
                'street' => '654 Maple Dr',
                'city' => 'Seattle',
                'state' => 'WA',
                'postal_code' => '98101',
                'country' => 'United States',
                'notes' => 'Previous client. May re-engage in Q2.',
            ],
        ];

        foreach ($contacts as $contactData) {
            ModuleRecordModel::create([
                'module_id' => $contactsModule->id,
                'data' => $contactData,
            ]);
        }

        $this->command->info("✓ Created {count($contacts)} sample contacts");
    }
}
