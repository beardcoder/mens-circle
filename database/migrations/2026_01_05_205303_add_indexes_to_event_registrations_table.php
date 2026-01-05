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
        Schema::table('event_registrations', function (Blueprint $table): void {
            // Composite index for confirmedRegistrations() query - filters by event_id and status
            $table->index(['event_id', 'status'], 'event_registrations_event_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_registrations', function (Blueprint $table): void {
            $table->dropIndex('event_registrations_event_status_index');
        });
    }
};
