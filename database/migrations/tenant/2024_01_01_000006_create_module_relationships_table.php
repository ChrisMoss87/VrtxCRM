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
        Schema::create('module_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_module_id')->constrained('modules')->onDelete('cascade');
            $table->foreignId('to_module_id')->constrained('modules')->onDelete('cascade');
            $table->string('type'); // one_to_many, many_to_many, polymorphic
            $table->string('name'); // Relationship name (e.g., "contacts", "deals")
            $table->string('inverse_name')->nullable(); // Inverse relationship name
            $table->json('settings')->nullable(); // Cascade delete, required, etc.
            $table->timestamps();

            // Indexes
            $table->index('from_module_id');
            $table->index('to_module_id');
            $table->unique(['from_module_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_relationships');
    }
};
