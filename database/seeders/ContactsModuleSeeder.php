<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Models\BlockModel;
use App\Infrastructure\Persistence\Eloquent\Models\FieldModel;
use App\Infrastructure\Persistence\Eloquent\Models\FieldOptionModel;
use App\Infrastructure\Persistence\Eloquent\Models\ModuleModel;
use Illuminate\Database\Seeder;

final class ContactsModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Contacts module
        $module = ModuleModel::create([
            'name' => 'Contacts',
            'singular_name' => 'Contact',
            'api_name' => 'contacts',
            'icon' => 'users',
            'description' => 'Manage your contacts and relationships',
            'is_active' => true,
            'is_system' => false,
            'settings' => [
                'enable_comments' => true,
                'enable_activities' => true,
                'enable_files' => true,
            ],
        ]);

        // Personal Information Block
        $personalBlock = BlockModel::create([
            'module_id' => $module->id,
            'name' => 'Personal Information',
            'api_name' => 'personal_info',
            'type' => 'section',
            'order' => 1,
            'settings' => [],
        ]);

        // First Name field
        FieldModel::create([
            'block_id' => $personalBlock->id,
            'label' => 'First Name',
            'api_name' => 'first_name',
            'type' => 'text',
            'order' => 1,
            'is_required' => true,
            'is_unique' => false,
            'is_searchable' => true,
            'settings' => [
                'width' => 50,
                'max_length' => 100,
            ],
        ]);

        // Last Name field
        FieldModel::create([
            'block_id' => $personalBlock->id,
            'label' => 'Last Name',
            'api_name' => 'last_name',
            'type' => 'text',
            'order' => 2,
            'is_required' => true,
            'is_unique' => false,
            'is_searchable' => true,
            'settings' => [
                'width' => 50,
                'max_length' => 100,
            ],
        ]);

        // Title/Position field
        FieldModel::create([
            'block_id' => $personalBlock->id,
            'label' => 'Title',
            'api_name' => 'title',
            'type' => 'text',
            'order' => 3,
            'is_required' => false,
            'is_unique' => false,
            'is_searchable' => true,
            'help_text' => 'Job title or position',
            'settings' => [
                'width' => 50,
                'max_length' => 100,
            ],
        ]);

        // Department field
        FieldModel::create([
            'block_id' => $personalBlock->id,
            'label' => 'Department',
            'api_name' => 'department',
            'type' => 'text',
            'order' => 4,
            'is_required' => false,
            'is_unique' => false,
            'is_searchable' => true,
            'settings' => [
                'width' => 50,
                'max_length' => 100,
            ],
        ]);

        // Contact Information Block
        $contactBlock = BlockModel::create([
            'module_id' => $module->id,
            'name' => 'Contact Information',
            'api_name' => 'contact_info',
            'type' => 'section',
            'order' => 2,
            'settings' => [],
        ]);

        // Email field
        FieldModel::create([
            'block_id' => $contactBlock->id,
            'label' => 'Email',
            'api_name' => 'email',
            'type' => 'email',
            'order' => 1,
            'is_required' => false,
            'is_unique' => true,
            'is_searchable' => true,
            'settings' => [
                'width' => 50,
            ],
        ]);

        // Phone field
        FieldModel::create([
            'block_id' => $contactBlock->id,
            'label' => 'Phone',
            'api_name' => 'phone',
            'type' => 'phone',
            'order' => 2,
            'is_required' => false,
            'is_unique' => false,
            'is_searchable' => true,
            'settings' => [
                'width' => 50,
            ],
        ]);

        // Mobile field
        FieldModel::create([
            'block_id' => $contactBlock->id,
            'label' => 'Mobile',
            'api_name' => 'mobile',
            'type' => 'phone',
            'order' => 3,
            'is_required' => false,
            'is_unique' => false,
            'is_searchable' => true,
            'settings' => [
                'width' => 50,
            ],
        ]);

        // LinkedIn field
        FieldModel::create([
            'block_id' => $contactBlock->id,
            'label' => 'LinkedIn',
            'api_name' => 'linkedin',
            'type' => 'url',
            'order' => 4,
            'is_required' => false,
            'is_unique' => false,
            'is_searchable' => false,
            'settings' => [
                'width' => 50,
            ],
        ]);

        // Address Block
        $addressBlock = BlockModel::create([
            'module_id' => $module->id,
            'name' => 'Address',
            'api_name' => 'address',
            'type' => 'section',
            'order' => 3,
            'settings' => [],
        ]);

        // Street field
        FieldModel::create([
            'block_id' => $addressBlock->id,
            'label' => 'Street',
            'api_name' => 'street',
            'type' => 'text',
            'order' => 1,
            'is_required' => false,
            'is_unique' => false,
            'is_searchable' => false,
            'settings' => [
                'width' => 100,
            ],
        ]);

        // City field
        FieldModel::create([
            'block_id' => $addressBlock->id,
            'label' => 'City',
            'api_name' => 'city',
            'type' => 'text',
            'order' => 2,
            'is_required' => false,
            'is_unique' => false,
            'is_searchable' => true,
            'settings' => [
                'width' => 50,
            ],
        ]);

        // State/Province field
        FieldModel::create([
            'block_id' => $addressBlock->id,
            'label' => 'State/Province',
            'api_name' => 'state',
            'type' => 'text',
            'order' => 3,
            'is_required' => false,
            'is_unique' => false,
            'is_searchable' => false,
            'settings' => [
                'width' => 25,
            ],
        ]);

        // Postal Code field
        FieldModel::create([
            'block_id' => $addressBlock->id,
            'label' => 'Postal Code',
            'api_name' => 'postal_code',
            'type' => 'text',
            'order' => 4,
            'is_required' => false,
            'is_unique' => false,
            'is_searchable' => false,
            'settings' => [
                'width' => 25,
            ],
        ]);

        // Country field
        FieldModel::create([
            'block_id' => $addressBlock->id,
            'label' => 'Country',
            'api_name' => 'country',
            'type' => 'text',
            'order' => 5,
            'is_required' => false,
            'is_unique' => false,
            'is_searchable' => false,
            'settings' => [
                'width' => 50,
            ],
        ]);

        // Additional Information Block
        $additionalBlock = BlockModel::create([
            'module_id' => $module->id,
            'name' => 'Additional Information',
            'api_name' => 'additional_info',
            'type' => 'section',
            'order' => 4,
            'settings' => [],
        ]);

        // Lead Source field
        $leadSourceField = FieldModel::create([
            'block_id' => $additionalBlock->id,
            'label' => 'Lead Source',
            'api_name' => 'lead_source',
            'type' => 'select',
            'order' => 1,
            'is_required' => false,
            'is_unique' => false,
            'is_searchable' => false,
            'settings' => [
                'width' => 50,
            ],
        ]);

        // Lead Source options
        $leadSourceOptions = [
            'Website',
            'Referral',
            'Social Media',
            'Event',
            'Cold Call',
            'Email Campaign',
            'Partner',
            'Other',
        ];

        foreach ($leadSourceOptions as $index => $option) {
            FieldOptionModel::create([
                'field_id' => $leadSourceField->id,
                'label' => $option,
                'value' => mb_strtolower(str_replace(' ', '_', $option)),
                'order' => $index + 1,
            ]);
        }

        // Contact Status field
        $statusField = FieldModel::create([
            'block_id' => $additionalBlock->id,
            'label' => 'Status',
            'api_name' => 'status',
            'type' => 'select',
            'order' => 2,
            'is_required' => false,
            'is_unique' => false,
            'is_searchable' => false,
            'default_value' => 'active',
            'settings' => [
                'width' => 50,
            ],
        ]);

        // Status options
        $statusOptions = [
            ['label' => 'Active', 'value' => 'active'],
            ['label' => 'Inactive', 'value' => 'inactive'],
            ['label' => 'Unqualified', 'value' => 'unqualified'],
        ];

        foreach ($statusOptions as $index => $option) {
            FieldOptionModel::create([
                'field_id' => $statusField->id,
                'label' => $option['label'],
                'value' => $option['value'],
                'order' => $index + 1,
            ]);
        }

        // Notes field
        FieldModel::create([
            'block_id' => $additionalBlock->id,
            'label' => 'Notes',
            'api_name' => 'notes',
            'type' => 'textarea',
            'order' => 3,
            'is_required' => false,
            'is_unique' => false,
            'is_searchable' => true,
            'help_text' => 'Additional notes about this contact',
            'settings' => [
                'width' => 100,
                'rows' => 4,
            ],
        ]);

        $this->command->info('✅ Contacts module created successfully!');
        $this->command->info("   - 4 blocks created");
        $this->command->info("   - 19 fields created");
        $this->command->info("   - Field options for Lead Source and Status created");
    }
}
