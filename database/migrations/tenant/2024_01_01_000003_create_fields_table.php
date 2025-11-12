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
        Schema::create('fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('block_id')->constrained()->onDelete('cascade');
            $table->string('type'); // text, number, select, date, etc. (20 types)
            $table->string('api_name');
            $table->string('label');
            $table->text('description')->nullable();
            $table->text('help_text')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_unique')->default(false);
            $table->boolean('is_searchable')->default(false);
            $table->integer('order')->default(0);
            $table->string('default_value')->nullable();
            $table->json('validation_rules')->nullable(); // Min, max, regex, etc.
            $table->json('settings')->nullable(); // Field-specific settings
            $table->integer('width')->default(100); // 25, 50, 75, 100 percent
            $table->timestamps();

            // Indexes
            $table->index(['block_id', 'order']);
            $table->index('type');
            $table->unique(['block_id', 'api_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fields');
    }
};
