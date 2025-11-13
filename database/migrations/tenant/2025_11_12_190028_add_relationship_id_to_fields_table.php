<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fields', function (Blueprint $table) {
            $table->foreignId('relationship_id')
                ->nullable()
                ->after('block_id')
                ->constrained('module_relationships')
                ->nullOnDelete();

            $table->index('relationship_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fields', function (Blueprint $table) {
            $table->dropForeign(['relationship_id']);
            $table->dropIndex(['relationship_id']);
            $table->dropColumn('relationship_id');
        });
    }
};
