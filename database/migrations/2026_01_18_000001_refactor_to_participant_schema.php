<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        // Step 1: Create participants table
        Schema::create('participants', function (Blueprint $table): void {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->timestamps();
        });

        // Step 2: Migrate data from event_registrations to participants
        // Use subquery to get the first registration per email for name/phone
        DB::statement("
            INSERT INTO participants (first_name, last_name, email, phone, created_at, updated_at)
            SELECT
                er.first_name,
                er.last_name,
                er.email,
                er.phone_number,
                er.created_at,
                er.updated_at
            FROM event_registrations er
            INNER JOIN (
                SELECT email, MIN(id) as first_id
                FROM event_registrations
                WHERE deleted_at IS NULL
                GROUP BY email
            ) first_reg ON er.id = first_reg.first_id
            WHERE er.deleted_at IS NULL
        ");

        // Step 3: Migrate data from newsletter_subscriptions (only emails not already in participants)
        DB::statement("
            INSERT INTO participants (first_name, last_name, email, created_at, updated_at)
            SELECT
                '',
                '',
                ns.email,
                ns.created_at,
                ns.updated_at
            FROM newsletter_subscriptions ns
            WHERE ns.deleted_at IS NULL
            AND NOT EXISTS (
                SELECT 1 FROM participants p WHERE p.email = ns.email
            )
        ");

        // Step 4: Add participant_id to event_registrations
        Schema::table('event_registrations', function (Blueprint $table): void {
            $table->unsignedBigInteger('participant_id')->nullable()->after('id');
            $table->timestamp('registered_at')->nullable()->after('status');
            $table->timestamp('cancelled_at')->nullable()->after('registered_at');
        });

        // Step 5: Update event_registrations with participant_id and registered_at
        DB::statement("
            UPDATE event_registrations
            SET
                participant_id = (SELECT id FROM participants WHERE participants.email = event_registrations.email),
                registered_at = confirmed_at
        ");

        // Step 6: Set cancelled_at for cancelled registrations
        DB::statement("
            UPDATE event_registrations
            SET cancelled_at = updated_at
            WHERE status = 'cancelled'
        ");

        // Step 7: Update status 'confirmed' to 'registered'
        DB::statement("
            UPDATE event_registrations
            SET status = 'registered'
            WHERE status = 'confirmed'
        ");

        // Step 8: Drop old indexes
        Schema::table('event_registrations', function (Blueprint $table): void {
            $table->dropIndex('event_registrations_event_status_index');
            $table->dropUnique(['event_id', 'email']);
        });

        // Step 9: Drop old columns
        Schema::table('event_registrations', function (Blueprint $table): void {
            $table->dropColumn(['first_name', 'last_name', 'email', 'phone_number', 'privacy_accepted', 'confirmed_at']);
        });

        // Step 10: Add foreign key and new indexes
        Schema::table('event_registrations', function (Blueprint $table): void {
            $table->foreign('participant_id')->references('id')->on('participants')->cascadeOnDelete();
            $table->unique(['participant_id', 'event_id']);
            $table->index(['event_id', 'status'], 'registrations_event_status_index');
        });

        // Step 11: Rename event_registrations to registrations
        Schema::rename('event_registrations', 'registrations');

        // Step 12: Add participant_id and confirmed_at to newsletter_subscriptions
        Schema::table('newsletter_subscriptions', function (Blueprint $table): void {
            $table->unsignedBigInteger('participant_id')->nullable()->after('id');
            $table->timestamp('confirmed_at')->nullable()->after('subscribed_at');
        });

        // Step 13: Update newsletter_subscriptions with participant_id
        DB::statement("
            UPDATE newsletter_subscriptions
            SET
                participant_id = (SELECT id FROM participants WHERE participants.email = newsletter_subscriptions.email),
                confirmed_at = subscribed_at
        ");

        // Step 14: Drop old indexes and columns from newsletter_subscriptions
        Schema::table('newsletter_subscriptions', function (Blueprint $table): void {
            $table->dropUnique(['email']);
        });

        Schema::table('newsletter_subscriptions', function (Blueprint $table): void {
            $table->dropColumn(['email', 'status']);
        });

        // Step 15: Add foreign key and unique constraint
        Schema::table('newsletter_subscriptions', function (Blueprint $table): void {
            $table->foreign('participant_id')->references('id')->on('participants')->cascadeOnDelete();
            $table->unique('participant_id');
        });
    }

    public function down(): void
    {
        // This migration is not reversible due to data transformation
        throw new RuntimeException('This migration cannot be reversed. Please restore from backup.');
    }
};
