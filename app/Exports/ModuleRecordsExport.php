<?php

declare(strict_types=1);

namespace App\Exports;

use App\Infrastructure\Persistence\Eloquent\Models\ModuleModel;
use App\Services\RecordService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

final class ModuleRecordsExport implements FromArray, WithHeadings, WithMapping, WithTitle
{
    private array $records;

    private array $columns;

    private ModuleModel $module;

    public function __construct(
        private readonly RecordService $recordService,
        int $moduleId,
        array $filters = [],
        array $sort = [],
        ?array $columns = null
    ) {
        // Load module with fields
        $this->module = ModuleModel::with(['blocks.fields'])->findOrFail($moduleId);

        // Get all columns if not specified
        if ($columns === null) {
            $this->columns = $this->getAllColumnNames();
        } else {
            $this->columns = $columns;
        }

        // Get all records without pagination
        $result = $this->recordService->getRecords(
            $moduleId,
            $filters,
            $sort,
            1,
            999999 // Large number to get all records
        );

        $this->records = $result['data'];
    }

    /**
     * Return records as array
     */
    public function array(): array
    {
        return $this->records;
    }

    /**
     * Define column headings
     */
    public function headings(): array
    {
        $headings = [];

        foreach ($this->columns as $column) {
            if ($column === 'id') {
                $headings[] = 'ID';
            } elseif ($column === 'created_at') {
                $headings[] = 'Created At';
            } elseif ($column === 'updated_at') {
                $headings[] = 'Updated At';
            } else {
                // Find field label
                $field = null;
                foreach ($this->module->blocks as $block) {
                    $field = $block->fields->firstWhere('api_name', $column);
                    if ($field) {
                        break;
                    }
                }

                $headings[] = $field ? $field->label : $column;
            }
        }

        return $headings;
    }

    /**
     * Map each record to row data
     */
    public function map($record): array
    {
        $row = [];

        foreach ($this->columns as $column) {
            if ($column === 'id') {
                $row[] = $record->id();
            } elseif ($column === 'created_at') {
                $row[] = $record->createdAt()?->format('Y-m-d H:i:s');
            } elseif ($column === 'updated_at') {
                $row[] = $record->updatedAt()?->format('Y-m-d H:i:s');
            } else {
                // Get value from data array
                $value = $record->data()[$column] ?? null;

                // Format arrays/objects as JSON
                if (is_array($value)) {
                    $row[] = json_encode($value);
                } else {
                    $row[] = $value;
                }
            }
        }

        return $row;
    }

    /**
     * Define sheet title
     */
    public function title(): string
    {
        return mb_substr($this->module->name, 0, 31); // Excel sheet name limit
    }

    /**
     * Get all column names from module fields
     */
    private function getAllColumnNames(): array
    {
        $columns = ['id'];

        foreach ($this->module->blocks as $block) {
            foreach ($block->fields as $field) {
                $columns[] = $field->api_name;
            }
        }

        $columns[] = 'created_at';
        $columns[] = 'updated_at';

        return $columns;
    }
}
