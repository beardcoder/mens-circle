<?php

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
        Schema::create('content_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // z.B. 'hero', 'intro', 'text_section'
            $table->jsonb('data'); // Alle block-spezifischen Daten
            $table->string('block_id')->unique(); // UUID für Media Library
            $table->unsignedInteger('order')->default(0); // Sortierreihenfolge
            $table->timestamps();

            $table->index(['page_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_blocks');
    }
};
