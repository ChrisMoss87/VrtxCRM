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
     * Seed core CRM modules.
     */
    public function run(): void
    {
        $this->seedContactsModule();
        $this->seedLeadsModule();
        $this->seedDealsModule();
        $this->seedCompaniesModule();
    }

    /**
     * Create Contacts module.
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
                            'api_name' => 'title',
                            'label' => 'Job Title',
                            'order' => 5,
                            'width' => 50,
                        ],
                        [
                            'type' => 'select',
                            'api_name' => 'status',
                            'label' => 'Status',
                            'is_required' => true,
                            'default_value' => 'active',
                            'order' => 6,
                            'width' => 50,
                            'options' => [
                                ['label' => 'Active', 'value' => 'active', 'color' => 'green', 'is_default' => true, 'order' => 1],
                                ['label' => 'Inactive', 'value' => 'inactive', 'color' => 'gray', 'order' => 2],
                                ['label' => 'Prospect', 'value' => 'prospect', 'color' => 'blue', 'order' => 3],
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'section',
                    'label' => 'Address',
                    'order' => 2,
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
                [
                    'type' => 'section',
                    'label' => 'Additional Information',
                    'order' => 3,
                    'settings' => ['columns' => 1, 'is_collapsible' => true],
                    'fields' => [
                        [
                            'type' => 'textarea',
                            'api_name' => 'notes',
                            'label' => 'Notes',
                            'help_text' => 'Internal notes about this contact',
                            'order' => 1,
                            'width' => 100,
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Create Leads module.
     */
    private function seedLeadsModule(): void
    {
        $this->moduleService->createModule([
            'name' => 'Leads',
            'singular_name' => 'Lead',
            'api_name' => 'leads',
            'icon' => 'target',
            'description' => 'Track and manage sales leads',
            'is_active' => true,
            'is_system' => true,
            'order' => 2,
            'blocks' => [
                [
                    'type' => 'section',
                    'label' => 'Lead Information',
                    'order' => 1,
                    'settings' => ['columns' => 2],
                    'fields' => [
                        [
                            'type' => 'text',
                            'api_name' => 'name',
                            'label' => 'Lead Name',
                            'is_required' => true,
                            'is_searchable' => true,
                            'order' => 1,
                            'width' => 50,
                        ],
                        [
                            'type' => 'text',
                            'api_name' => 'company',
                            'label' => 'Company',
                            'is_searchable' => true,
                            'order' => 2,
                            'width' => 50,
                        ],
                        [
                            'type' => 'email',
                            'api_name' => 'email',
                            'label' => 'Email',
                            'is_required' => true,
                            'is_searchable' => true,
                            'order' => 3,
                            'width' => 50,
                        ],
                        [
                            'type' => 'phone',
                            'api_name' => 'phone',
                            'label' => 'Phone',
                            'order' => 4,
                            'width' => 50,
                        ],
                        [
                            'type' => 'select',
                            'api_name' => 'status',
                            'label' => 'Status',
                            'is_required' => true,
                            'default_value' => 'new',
                            'order' => 5,
                            'width' => 50,
                            'options' => [
                                ['label' => 'New', 'value' => 'new', 'color' => 'blue', 'is_default' => true, 'order' => 1],
                                ['label' => 'Contacted', 'value' => 'contacted', 'color' => 'yellow', 'order' => 2],
                                ['label' => 'Qualified', 'value' => 'qualified', 'color' => 'green', 'order' => 3],
                                ['label' => 'Unqualified', 'value' => 'unqualified', 'color' => 'red', 'order' => 4],
                                ['label' => 'Converted', 'value' => 'converted', 'color' => 'purple', 'order' => 5],
                            ],
                        ],
                        [
                            'type' => 'select',
                            'api_name' => 'source',
                            'label' => 'Lead Source',
                            'order' => 6,
                            'width' => 50,
                            'options' => [
                                ['label' => 'Website', 'value' => 'website', 'order' => 1],
                                ['label' => 'Referral', 'value' => 'referral', 'order' => 2],
                                ['label' => 'Social Media', 'value' => 'social_media', 'order' => 3],
                                ['label' => 'Email Campaign', 'value' => 'email_campaign', 'order' => 4],
                                ['label' => 'Trade Show', 'value' => 'trade_show', 'order' => 5],
                                ['label' => 'Other', 'value' => 'other', 'order' => 6],
                            ],
                        ],
                        [
                            'type' => 'currency',
                            'api_name' => 'estimated_value',
                            'label' => 'Estimated Value',
                            'help_text' => 'Potential deal value',
                            'order' => 7,
                            'width' => 50,
                        ],
                        [
                            'type' => 'date',
                            'api_name' => 'expected_close_date',
                            'label' => 'Expected Close Date',
                            'order' => 8,
                            'width' => 50,
                        ],
                    ],
                ],
                [
                    'type' => 'section',
                    'label' => 'Additional Details',
                    'order' => 2,
                    'settings' => ['columns' => 1, 'is_collapsible' => true],
                    'fields' => [
                        [
                            'type' => 'textarea',
                            'api_name' => 'description',
                            'label' => 'Description',
                            'help_text' => 'Describe the lead opportunity',
                            'order' => 1,
                            'width' => 100,
                        ],
                        [
                            'type' => 'textarea',
                            'api_name' => 'notes',
                            'label' => 'Notes',
                            'order' => 2,
                            'width' => 100,
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Create Deals module.
     */
    private function seedDealsModule(): void
    {
        $this->moduleService->createModule([
            'name' => 'Deals',
            'singular_name' => 'Deal',
            'api_name' => 'deals',
            'icon' => 'dollar-sign',
            'description' => 'Manage sales opportunities and pipeline',
            'is_active' => true,
            'is_system' => true,
            'order' => 3,
            'blocks' => [
                [
                    'type' => 'section',
                    'label' => 'Deal Information',
                    'order' => 1,
                    'settings' => ['columns' => 2],
                    'fields' => [
                        [
                            'type' => 'text',
                            'api_name' => 'name',
                            'label' => 'Deal Name',
                            'is_required' => true,
                            'is_searchable' => true,
                            'order' => 1,
                            'width' => 100,
                        ],
                        [
                            'type' => 'currency',
                            'api_name' => 'amount',
                            'label' => 'Amount',
                            'is_required' => true,
                            'order' => 2,
                            'width' => 50,
                        ],
                        [
                            'type' => 'date',
                            'api_name' => 'close_date',
                            'label' => 'Expected Close Date',
                            'is_required' => true,
                            'order' => 3,
                            'width' => 50,
                        ],
                        [
                            'type' => 'select',
                            'api_name' => 'stage',
                            'label' => 'Stage',
                            'is_required' => true,
                            'default_value' => 'qualification',
                            'order' => 4,
                            'width' => 50,
                            'options' => [
                                ['label' => 'Qualification', 'value' => 'qualification', 'color' => 'blue', 'is_default' => true, 'order' => 1],
                                ['label' => 'Proposal', 'value' => 'proposal', 'color' => 'yellow', 'order' => 2],
                                ['label' => 'Negotiation', 'value' => 'negotiation', 'color' => 'orange', 'order' => 3],
                                ['label' => 'Closed Won', 'value' => 'closed_won', 'color' => 'green', 'order' => 4],
                                ['label' => 'Closed Lost', 'value' => 'closed_lost', 'color' => 'red', 'order' => 5],
                            ],
                        ],
                        [
                            'type' => 'percent',
                            'api_name' => 'probability',
                            'label' => 'Probability',
                            'help_text' => 'Likelihood of closing (0-100)',
                            'default_value' => '50',
                            'order' => 5,
                            'width' => 50,
                        ],
                        [
                            'type' => 'select',
                            'api_name' => 'type',
                            'label' => 'Deal Type',
                            'order' => 6,
                            'width' => 50,
                            'options' => [
                                ['label' => 'New Business', 'value' => 'new_business', 'order' => 1],
                                ['label' => 'Existing Business', 'value' => 'existing_business', 'order' => 2],
                                ['label' => 'Renewal', 'value' => 'renewal', 'order' => 3],
                                ['label' => 'Upsell', 'value' => 'upsell', 'order' => 4],
                            ],
                        ],
                        [
                            'type' => 'select',
                            'api_name' => 'priority',
                            'label' => 'Priority',
                            'default_value' => 'medium',
                            'order' => 7,
                            'width' => 50,
                            'options' => [
                                ['label' => 'Low', 'value' => 'low', 'color' => 'gray', 'order' => 1],
                                ['label' => 'Medium', 'value' => 'medium', 'color' => 'blue', 'is_default' => true, 'order' => 2],
                                ['label' => 'High', 'value' => 'high', 'color' => 'orange', 'order' => 3],
                                ['label' => 'Critical', 'value' => 'critical', 'color' => 'red', 'order' => 4],
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'section',
                    'label' => 'Additional Information',
                    'order' => 2,
                    'settings' => ['columns' => 1, 'is_collapsible' => true],
                    'fields' => [
                        [
                            'type' => 'textarea',
                            'api_name' => 'description',
                            'label' => 'Description',
                            'order' => 1,
                            'width' => 100,
                        ],
                        [
                            'type' => 'textarea',
                            'api_name' => 'notes',
                            'label' => 'Notes',
                            'order' => 2,
                            'width' => 100,
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Create Companies module.
     */
    private function seedCompaniesModule(): void
    {
        $this->moduleService->createModule([
            'name' => 'Companies',
            'singular_name' => 'Company',
            'api_name' => 'companies',
            'icon' => 'building',
            'description' => 'Manage company accounts and organizations',
            'is_active' => true,
            'is_system' => true,
            'order' => 4,
            'blocks' => [
                [
                    'type' => 'section',
                    'label' => 'Company Information',
                    'order' => 1,
                    'settings' => ['columns' => 2],
                    'fields' => [
                        [
                            'type' => 'text',
                            'api_name' => 'name',
                            'label' => 'Company Name',
                            'is_required' => true,
                            'is_searchable' => true,
                            'order' => 1,
                            'width' => 50,
                        ],
                        [
                            'type' => 'url',
                            'api_name' => 'website',
                            'label' => 'Website',
                            'order' => 2,
                            'width' => 50,
                        ],
                        [
                            'type' => 'email',
                            'api_name' => 'email',
                            'label' => 'Email',
                            'is_searchable' => true,
                            'order' => 3,
                            'width' => 50,
                        ],
                        [
                            'type' => 'phone',
                            'api_name' => 'phone',
                            'label' => 'Phone',
                            'order' => 4,
                            'width' => 50,
                        ],
                        [
                            'type' => 'select',
                            'api_name' => 'industry',
                            'label' => 'Industry',
                            'order' => 5,
                            'width' => 50,
                            'options' => [
                                ['label' => 'Technology', 'value' => 'technology', 'order' => 1],
                                ['label' => 'Finance', 'value' => 'finance', 'order' => 2],
                                ['label' => 'Healthcare', 'value' => 'healthcare', 'order' => 3],
                                ['label' => 'Manufacturing', 'value' => 'manufacturing', 'order' => 4],
                                ['label' => 'Retail', 'value' => 'retail', 'order' => 5],
                                ['label' => 'Real Estate', 'value' => 'real_estate', 'order' => 6],
                                ['label' => 'Other', 'value' => 'other', 'order' => 7],
                            ],
                        ],
                        [
                            'type' => 'select',
                            'api_name' => 'size',
                            'label' => 'Company Size',
                            'order' => 6,
                            'width' => 50,
                            'options' => [
                                ['label' => '1-10 employees', 'value' => '1-10', 'order' => 1],
                                ['label' => '11-50 employees', 'value' => '11-50', 'order' => 2],
                                ['label' => '51-200 employees', 'value' => '51-200', 'order' => 3],
                                ['label' => '201-500 employees', 'value' => '201-500', 'order' => 4],
                                ['label' => '501-1000 employees', 'value' => '501-1000', 'order' => 5],
                                ['label' => '1000+ employees', 'value' => '1000+', 'order' => 6],
                            ],
                        ],
                        [
                            'type' => 'currency',
                            'api_name' => 'annual_revenue',
                            'label' => 'Annual Revenue',
                            'order' => 7,
                            'width' => 50,
                        ],
                    ],
                ],
                [
                    'type' => 'section',
                    'label' => 'Address',
                    'order' => 2,
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
                [
                    'type' => 'section',
                    'label' => 'Additional Information',
                    'order' => 3,
                    'settings' => ['columns' => 1, 'is_collapsible' => true],
                    'fields' => [
                        [
                            'type' => 'textarea',
                            'api_name' => 'description',
                            'label' => 'Description',
                            'order' => 1,
                            'width' => 100,
                        ],
                        [
                            'type' => 'textarea',
                            'api_name' => 'notes',
                            'label' => 'Notes',
                            'order' => 2,
                            'width' => 100,
                        ],
                    ],
                ],
            ],
        ]);
    }
}
