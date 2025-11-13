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
        Schema::create('table_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('module'); // e.g., 'contacts', 'leads'
            $table->text('description')->nullable();
            $table->json('filters')->nullable(); // Saved filter configuration
            $table->json('sorting')->nullable(); // Saved sort configuration
            $table->json('column_visibility')->nullable(); // Which columns are visible
            $table->json('column_order')->nullable(); // Column order
            $table->json('column_widths')->nullable(); // Column widths
            $table->integer('page_size')->default(50);
            $table->boolean('is_default')->default(false); // Default view for this user
            $table->boolean('is_public')->default(false); // Public/shared view
            $table->timestamps();

            $table->index(['user_id', 'module']);
            $table->index(['module', 'is_public']);
        });

        Schema::create('table_view_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_view_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('can_edit')->default(false);
            $table->timestamps();

            $table->unique(['table_view_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_view_shares');
        Schema::dropIfExists('table_views');
    }
};
