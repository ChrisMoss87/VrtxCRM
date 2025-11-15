<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Models\ModuleModel;
use App\Infrastructure\Persistence\Eloquent\Models\ModuleRecordModel;
use Illuminate\Database\Seeder;

final class SampleContactsSeeder extends Seeder
{
    /**
     * Seed 100 sample contact records with all field types for testing.
     */
    public function run(): void
    {
        // Get the Contacts module
        $contactsModule = ModuleModel::where('api_name', 'contacts')->first();

        if (! $contactsModule) {
            $this->command->error('Contacts module not found. Run ModuleSeeder first.');

            return;
        }

        $this->command->info('Generating 100 sample contacts...');

        // Generate 100 contacts
        for ($i = 1; $i <= 100; $i++) {
            $contactData = $this->generateContactData($i);

            ModuleRecordModel::create([
                'module_id' => $contactsModule->id,
                'data' => $contactData,
            ]);
        }

        $this->command->info('✓ Created 100 sample contacts with all field types');
    }

    /**
     * Generate realistic contact data for a single contact.
     */
    private function generateContactData(int $index): array
    {
        $firstNames = ['John', 'Jane', 'Michael', 'Sarah', 'David', 'Emma', 'James', 'Olivia', 'Robert', 'Sophia', 'William', 'Isabella', 'Richard', 'Mia', 'Joseph', 'Charlotte', 'Thomas', 'Amelia', 'Christopher', 'Harper'];
        $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin'];

        $jobTitles = ['CEO', 'CTO', 'VP of Sales', 'Marketing Manager', 'Senior Developer', 'Product Manager', 'Designer', 'Sales Director', 'Account Executive', 'Business Analyst', 'Operations Manager', 'Customer Success Manager', 'Software Engineer', 'Data Scientist', 'Project Manager'];

        $cities = ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'Philadelphia', 'San Antonio', 'San Diego', 'Dallas', 'San Jose', 'Austin', 'Jacksonville', 'Fort Worth', 'Columbus', 'Charlotte', 'Seattle', 'Denver', 'Boston', 'Portland', 'Miami'];

        $states = ['NY', 'CA', 'IL', 'TX', 'AZ', 'PA', 'TX', 'CA', 'TX', 'CA', 'TX', 'FL', 'TX', 'OH', 'NC', 'WA', 'CO', 'MA', 'OR', 'FL'];

        $statuses = ['active', 'inactive', 'prospect', 'customer'];
        $contactTypes = ['individual', 'business', 'partner'];
        $allInterests = ['technology', 'marketing', 'sales', 'design', 'engineering'];

        $firstName = $firstNames[($index - 1) % count($firstNames)];
        $lastName = $lastNames[(int) (($index - 1) / count($firstNames)) % count($lastNames)];
        $cityIndex = $index % count($cities);

        // Basic Information
        $data = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => mb_strtolower($firstName.'.'.$lastName.'.'.$index.'@example.com'),
            'phone' => '+1 (555) '.str_pad((string) (100 + $index), 3, '0', STR_PAD_LEFT).'-'.str_pad((string) (1000 + $index), 4, '0', STR_PAD_LEFT),
            'job_title' => $jobTitles[$index % count($jobTitles)],
            'linkedin_url' => 'https://linkedin.com/in/'.mb_strtolower($firstName.$lastName.$index),
        ];

        // Status & Classification
        $data['status'] = $statuses[$index % count($statuses)];
        $data['contact_type'] = $contactTypes[$index % count($contactTypes)];

        // Multiselect: random 1-3 interests
        $interestCount = ($index % 3) + 1;
        $selectedInterests = array_slice($allInterests, 0, $interestCount);
        $data['interests'] = $selectedInterests;

        $data['is_vip'] = ($index % 5) === 0; // Every 5th contact is VIP
        $data['email_opt_in'] = ($index % 3) !== 0; // 2/3 opted in

        // Numeric Fields
        $data['interactions_count'] = $index * 3; // 3, 6, 9, etc.
        $data['satisfaction_score'] = round(3.0 + ($index % 20) / 10, 1); // 3.0 to 5.0
        $data['lifetime_value'] = ($index * 1000) + (($index % 10) * 500); // Varied values
        $data['engagement_rate'] = 20 + ($index % 80); // 20% to 99%

        // Date & Time Fields
        $birthYear = 1960 + ($index % 40); // 1960 to 1999
        $data['birth_date'] = sprintf('%04d-%02d-%02d', $birthYear, ($index % 12) + 1, ($index % 28) + 1);

        $firstContactYear = 2020 + ($index % 5); // 2020 to 2024
        $data['first_contact_date'] = sprintf('%04d-%02d-%02d', $firstContactYear, ($index % 12) + 1, ($index % 28) + 1);

        // Datetime: Recent interaction within last 30 days
        $daysAgo = $index % 30;
        $data['last_interaction'] = date('Y-m-d H:i:s', strtotime("-{$daysAgo} days"));

        // Time: Preferred contact time (9 AM to 5 PM)
        $hour = 9 + ($index % 9);
        $data['preferred_contact_time'] = sprintf('%02d:00:00', $hour);

        // Address
        $data['street'] = ($index * 100).' '.['Main St', 'Oak Ave', 'Elm St', 'Pine Rd', 'Maple Dr'][$index % 5];
        $data['city'] = $cities[$cityIndex];
        $data['state'] = $states[$cityIndex];
        $data['postal_code'] = str_pad((string) (10000 + $index), 5, '0', STR_PAD_LEFT);
        $data['country'] = 'United States';

        // Text fields
        $data['bio'] = $firstName.' '.$lastName.' is a '.$data['job_title'].' based in '.$data['city'].', '.$data['state'].'. With over '.($index % 15 + 1).' years of experience in the industry, they bring valuable expertise to the table.';

        $data['notes'] = 'Contact #'.$index.'. '.(['Very responsive', 'Needs follow-up', 'Key decision maker', 'Interested in premium features', 'Budget constrained'][$index % 5]);

        return $data;
    }
}
