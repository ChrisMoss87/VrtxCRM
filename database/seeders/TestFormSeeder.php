<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Models\BlockModel;
use App\Infrastructure\Persistence\Eloquent\Models\FieldModel;
use App\Infrastructure\Persistence\Eloquent\Models\FieldOptionModel;
use App\Infrastructure\Persistence\Eloquent\Models\ModuleModel;
use Illuminate\Database\Seeder;

final class TestFormSeeder extends Seeder
{
    public function run(): void
    {
        $module = ModuleModel::create([
            'name' => 'Test Form',
            'singular_name' => 'Test Form',
            'icon' => 'clipboard',
            'description' => 'Test form for field rendering',
            'is_active' => true,
            'order' => 999,
        ]);

        $block = BlockModel::create([
            'module_id' => $module->id,
            'name' => 'Test Fields',
            'type' => 'section',
            'order' => 1,
            'columns' => 2,
            'is_collapsible' => false,
            'is_collapsed_by_default' => false,
        ]);

        FieldModel::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'First Name',
            'api_name' => 'first_name',
            'type' => 'text',
            'description' => 'Enter your first name',
            'is_required' => true,
            'order' => 1,
            'width' => 50,
        ]);

        FieldModel::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'Last Name',
            'api_name' => 'last_name',
            'type' => 'text',
            'description' => 'Enter your last name',
            'is_required' => true,
            'order' => 2,
            'width' => 50,
        ]);

        FieldModel::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'Email',
            'api_name' => 'email',
            'type' => 'email',
            'description' => 'Your email address',
            'is_required' => true,
            'order' => 3,
            'width' => 100,
        ]);

        $statusField = FieldModel::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'Status',
            'api_name' => 'status',
            'type' => 'select',
            'description' => 'Select your status',
            'is_required' => false,
            'order' => 4,
            'width' => 50,
        ]);

        FieldOptionModel::create([
            'field_id' => $statusField->id,
            'label' => 'Active',
            'value' => 'active',
            'order' => 1,
        ]);

        FieldOptionModel::create([
            'field_id' => $statusField->id,
            'label' => 'Inactive',
            'value' => 'inactive',
            'order' => 2,
        ]);

        FieldOptionModel::create([
            'field_id' => $statusField->id,
            'label' => 'Pending',
            'value' => 'pending',
            'order' => 3,
        ]);

        FieldModel::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'Bio',
            'api_name' => 'bio',
            'type' => 'textarea',
            'description' => 'Tell us about yourself',
            'is_required' => false,
            'order' => 5,
            'width' => 100,
        ]);
    }
}
