<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Services\FieldService;
use App\Services\ModuleService;
use Illuminate\Database\Seeder;

final class ModuleSeeder extends Seeder
{
    public function __construct(
        private readonly ModuleService $moduleService,
        private readonly FieldService $fieldService
    ) {}

    /**
     * Seed Contacts module with ALL field types for testing.
     */
    public function run(): void
    {
        $this->seedContactsModule();
    }

    /**
     * Create Contacts module with all available field types.
     */
    private function seedContactsModule(): void
    {
        $this->moduleService->createModule([
            'name' => 'Contacts',
            'singular_name' => 'Contact',
            'api_name' => 'contacts',
            'icon' => 'users',
            'description' => 'Manage your contacts and customer relationships',
            'is_active' => true,
            'is_system' => true,
            'order' => 1,
            'blocks' => [
                // Basic Information Section
                [
                    'type' => 'section',
                    'label' => 'Basic Information',
                    'order' => 1,
                    'settings' => ['columns' => 2],
                    'fields' => [
                        [
                            'type' => 'text',
                            'api_name' => 'first_name',
                            'label' => 'First Name',
                            'is_required' => true,
                            'is_searchable' => true,
                            'order' => 1,
                            'width' => 50,
                        ],
                        [
                            'type' => 'text',
                            'api_name' => 'last_name',
                            'label' => 'Last Name',
                            'is_required' => true,
                            'is_searchable' => true,
                            'order' => 2,
                            'width' => 50,
                        ],
                        [
                            'type' => 'email',
                            'api_name' => 'email',
                            'label' => 'Email',
                            'is_required' => true,
                            'is_unique' => true,
                            'is_searchable' => true,
                            'order' => 3,
                            'width' => 50,
                        ],
                        [
                            'type' => 'phone',
                            'api_name' => 'phone',
                            'label' => 'Phone',
                            'is_searchable' => true,
                            'order' => 4,
                            'width' => 50,
                        ],
                        [
                            'type' => 'text',
                            'api_name' => 'job_title',
                            'label' => 'Job Title',
                            'order' => 5,
                            'width' => 50,
                        ],
                        [
                            'type' => 'url',
                            'api_name' => 'linkedin_url',
                            'label' => 'LinkedIn URL',
                            'help_text' => 'Professional LinkedIn profile',
                            'order' => 6,
                            'width' => 50,
                        ],
                    ],
                ],

                // Status & Classification Section
                [
                    'type' => 'section',
                    'label' => 'Status & Classification',
                    'order' => 2,
                    'settings' => ['columns' => 2],
                    'fields' => [
                        [
                            'type' => 'select',
                            'api_name' => 'status',
                            'label' => 'Status',
                            'is_required' => true,
                            'default_value' => 'active',
                            'order' => 1,
                            'width' => 50,
                            'options' => [
                                ['label' => 'Active', 'value' => 'active', 'color' => 'green', 'is_default' => true, 'order' => 1],
                                ['label' => 'Inactive', 'value' => 'inactive', 'color' => 'gray', 'order' => 2],
                                ['label' => 'Prospect', 'value' => 'prospect', 'color' => 'blue', 'order' => 3],
                                ['label' => 'Customer', 'value' => 'customer', 'color' => 'purple', 'order' => 4],
                            ],
                        ],
                        [
                            'type' => 'radio',
                            'api_name' => 'contact_type',
                            'label' => 'Contact Type',
                            'default_value' => 'individual',
                            'order' => 2,
                            'width' => 50,
                            'options' => [
                                ['label' => 'Individual', 'value' => 'individual', 'is_default' => true, 'order' => 1],
                                ['label' => 'Business', 'value' => 'business', 'order' => 2],
                                ['label' => 'Partner', 'value' => 'partner', 'order' => 3],
                            ],
                        ],
                        [
                            'type' => 'multiselect',
                            'api_name' => 'interests',
                            'label' => 'Interests',
                            'help_text' => 'Select all that apply',
                            'order' => 3,
                            'width' => 50,
                            'options' => [
                                ['label' => 'Technology', 'value' => 'technology', 'order' => 1],
                                ['label' => 'Marketing', 'value' => 'marketing', 'order' => 2],
                                ['label' => 'Sales', 'value' => 'sales', 'order' => 3],
                                ['label' => 'Design', 'value' => 'design', 'order' => 4],
                                ['label' => 'Engineering', 'value' => 'engineering', 'order' => 5],
                            ],
                        ],
                        [
                            'type' => 'checkbox',
                            'api_name' => 'is_vip',
                            'label' => 'VIP Contact',
                            'default_value' => false,
                            'order' => 4,
                            'width' => 25,
                        ],
                        [
                            'type' => 'toggle',
                            'api_name' => 'email_opt_in',
                            'label' => 'Email Opt-In',
                            'help_text' => 'Subscribed to email communications',
                            'default_value' => true,
                            'order' => 5,
                            'width' => 25,
                        ],
                    ],
                ],

                // Numeric Fields Section
                [
                    'type' => 'section',
                    'label' => 'Metrics & Values',
                    'order' => 3,
                    'settings' => ['columns' => 2, 'is_collapsible' => true],
                    'fields' => [
                        [
                            'type' => 'number',
                            'api_name' => 'interactions_count',
                            'label' => 'Total Interactions',
                            'help_text' => 'Number of interactions with this contact',
                            'default_value' => '0',
                            'order' => 1,
                            'width' => 50,
                        ],
                        [
                            'type' => 'decimal',
                            'api_name' => 'satisfaction_score',
                            'label' => 'Satisfaction Score',
                            'help_text' => 'Customer satisfaction rating (0.0 - 5.0)',
                            'order' => 2,
                            'width' => 50,
                        ],
                        [
                            'type' => 'currency',
                            'api_name' => 'lifetime_value',
                            'label' => 'Lifetime Value',
                            'help_text' => 'Total revenue from this contact',
                            'order' => 3,
                            'width' => 50,
                        ],
                        [
                            'type' => 'percent',
                            'api_name' => 'engagement_rate',
                            'label' => 'Engagement Rate',
                            'help_text' => 'Email/content engagement percentage',
                            'order' => 4,
                            'width' => 50,
                        ],
                    ],
                ],

                // Date & Time Section
                [
                    'type' => 'section',
                    'label' => 'Important Dates',
                    'order' => 4,
                    'settings' => ['columns' => 2, 'is_collapsible' => true],
                    'fields' => [
                        [
                            'type' => 'date',
                            'api_name' => 'birth_date',
                            'label' => 'Birth Date',
                            'order' => 1,
                            'width' => 50,
                        ],
                        [
                            'type' => 'date',
                            'api_name' => 'first_contact_date',
                            'label' => 'First Contact Date',
                            'help_text' => 'Date of first interaction',
                            'order' => 2,
                            'width' => 50,
                        ],
                        [
                            'type' => 'datetime',
                            'api_name' => 'last_interaction',
                            'label' => 'Last Interaction',
                            'help_text' => 'Most recent contact timestamp',
                            'order' => 3,
                            'width' => 50,
                        ],
                        [
                            'type' => 'time',
                            'api_name' => 'preferred_contact_time',
                            'label' => 'Preferred Contact Time',
                            'help_text' => 'Best time to reach this contact',
                            'order' => 4,
                            'width' => 50,
                        ],
                    ],
                ],

                // Address Section
                [
                    'type' => 'section',
                    'label' => 'Address',
                    'order' => 5,
                    'settings' => ['columns' => 2, 'is_collapsible' => true],
                    'fields' => [
                        [
                            'type' => 'text',
                            'api_name' => 'street',
                            'label' => 'Street Address',
                            'order' => 1,
                            'width' => 100,
                        ],
                        [
                            'type' => 'text',
                            'api_name' => 'city',
                            'label' => 'City',
                            'order' => 2,
                            'width' => 50,
                        ],
                        [
                            'type' => 'text',
                            'api_name' => 'state',
                            'label' => 'State/Province',
                            'order' => 3,
                            'width' => 25,
                        ],
                        [
                            'type' => 'text',
                            'api_name' => 'postal_code',
                            'label' => 'Postal Code',
                            'order' => 4,
                            'width' => 25,
                        ],
                        [
                            'type' => 'text',
                            'api_name' => 'country',
                            'label' => 'Country',
                            'order' => 5,
                            'width' => 50,
                        ],
                    ],
                ],

                // Additional Information Section
                [
                    'type' => 'section',
                    'label' => 'Additional Information',
                    'order' => 6,
                    'settings' => ['columns' => 1, 'is_collapsible' => true],
                    'fields' => [
                        [
                            'type' => 'textarea',
                            'api_name' => 'bio',
                            'label' => 'Biography',
                            'help_text' => 'Brief background about this contact',
                            'order' => 1,
                            'width' => 100,
                        ],
                        [
                            'type' => 'textarea',
                            'api_name' => 'notes',
                            'label' => 'Notes',
                            'help_text' => 'Internal notes about this contact',
                            'order' => 2,
                            'width' => 100,
                        ],
                    ],
                ],
            ],
        ]);
    }
}
