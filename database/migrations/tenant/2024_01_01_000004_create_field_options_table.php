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
        Schema::create('field_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_id')->constrained()->onDelete('cascade');
            $table->string('label');
            $table->string('value');
            $table->string('color')->nullable(); // For visual styling
            $table->integer('order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            // Indexes
            $table->index(['field_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('field_options');
    }
};
