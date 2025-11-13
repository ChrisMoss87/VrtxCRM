<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TableViewShare extends Model
{
    protected $fillable = [
        'table_view_id',
        'user_id',
        'can_edit',
    ];

    protected $casts = [
        'can_edit' => 'boolean',
    ];

    public function tableView(): BelongsTo
    {
        return $this->belongsTo(TableView::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
