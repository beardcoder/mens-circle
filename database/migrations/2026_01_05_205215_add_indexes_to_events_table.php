<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            // Composite index for showNext() query - filters by is_published and orders by event_date
            $table->index(['is_published', 'event_date'], 'events_published_date_index');

            // Index for slug lookup (already unique, but adding explicit index for clarity)
            // Note: unique constraint already creates an index, so we skip this
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->dropIndex('events_published_date_index');
        });
    }
};
