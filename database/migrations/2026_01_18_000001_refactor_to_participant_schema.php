<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        // Step 1: Create participants table (if not exists)
        if (! Schema::hasTable('participants')) {
            Schema::create('participants', function (Blueprint $table): void {
                $table->id();
                $table->string('first_name');
                $table->string('last_name');
                $table->string('email')->unique();
                $table->string('phone')->nullable();
                $table->timestamps();
            });
        }

        // Determine which source table to use
        $sourceTable = Schema::hasTable('event_registrations') ? 'event_registrations' : 'registrations';
        $hasOldColumns = Schema::hasColumn($sourceTable, 'first_name');

        // Step 2: Migrate data from event_registrations to participants (if not done yet)
        if ($hasOldColumns && DB::table('participants')->count() === 0) {
            DB::statement("
                INSERT INTO participants (first_name, last_name, email, phone, created_at, updated_at)
                SELECT
                    er.first_name,
                    er.last_name,
                    er.email,
                    er.phone_number,
                    er.created_at,
                    er.updated_at
                FROM {$sourceTable} er
                INNER JOIN (
                    SELECT email, MIN(id) as first_id
                    FROM {$sourceTable}
                    WHERE deleted_at IS NULL
                    GROUP BY email
                ) first_reg ON er.id = first_reg.first_id
                WHERE er.deleted_at IS NULL
            ");

            // Step 3: Migrate data from newsletter_subscriptions (only emails not already in participants)
            if (Schema::hasColumn('newsletter_subscriptions', 'email')) {
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
            }
        }

        // Step 4: Add participant_id to event_registrations/registrations (if not exists)
        if (! Schema::hasColumn($sourceTable, 'participant_id')) {
            Schema::table($sourceTable, function (Blueprint $table): void {
                $table->unsignedBigInteger('participant_id')->nullable()->after('id');
            });
        }

        if (! Schema::hasColumn($sourceTable, 'registered_at')) {
            Schema::table($sourceTable, function (Blueprint $table): void {
                $table->timestamp('registered_at')->nullable()->after('status');
            });
        }

        if (! Schema::hasColumn($sourceTable, 'cancelled_at')) {
            Schema::table($sourceTable, function (Blueprint $table): void {
                $table->timestamp('cancelled_at')->nullable()->after('registered_at');
            });
        }

        // Step 5: Update registrations with participant_id (if still has old columns)
        if ($hasOldColumns) {
            DB::statement("
                UPDATE {$sourceTable}
                SET
                    participant_id = (SELECT id FROM participants WHERE participants.email = {$sourceTable}.email),
                    registered_at = confirmed_at
                WHERE participant_id IS NULL
            ");

            // Step 6: Set cancelled_at for cancelled registrations
            DB::statement("
                UPDATE {$sourceTable}
                SET cancelled_at = updated_at
                WHERE status = 'cancelled' AND cancelled_at IS NULL
            ");

            // Step 7: Update status 'confirmed' to 'registered'
            DB::statement("
                UPDATE {$sourceTable}
                SET status = 'registered'
                WHERE status = 'confirmed'
            ");

            // Step 8: Drop old indexes (check if they exist first)
            try {
                Schema::table($sourceTable, function (Blueprint $table): void {
                    $table->dropIndex('event_registrations_event_status_index');
                });
            } catch (Exception $e) {
                // Index doesn't exist, continue
            }

            try {
                Schema::table($sourceTable, function (Blueprint $table): void {
                    $table->dropUnique(['event_id', 'email']);
                });
            } catch (Exception $e) {
                // Unique constraint doesn't exist, continue
            }

            // Step 9: Drop old columns
            $columnsToDrop = array_filter(
                ['first_name', 'last_name', 'email', 'phone_number', 'privacy_accepted', 'confirmed_at'],
                fn (string $col) => Schema::hasColumn($sourceTable, $col),
            );

            if ($columnsToDrop !== []) {
                Schema::table($sourceTable, function (Blueprint $table) use ($columnsToDrop): void {
                    $table->dropColumn($columnsToDrop);
                });
            }
        }

        // Step 10: Add foreign key and new indexes (if not exists)
        // Skip FK check for SQLite as it doesn't have information_schema
        try {
            Schema::table($sourceTable, function (Blueprint $table): void {
                $table->foreign('participant_id')->references('id')->on('participants')->cascadeOnDelete();
            });
        } catch (Exception $exception) {
            // Foreign key already exists or cannot be created
        }

        // Add unique constraint if it doesn't exist
        try {
            Schema::table($sourceTable, function (Blueprint $table): void {
                $table->unique(['participant_id', 'event_id']);
            });
        } catch (Exception $exception) {
            // Unique constraint already exists
        }

        // Add new index if it doesn't exist
        try {
            Schema::table($sourceTable, function (Blueprint $table): void {
                $table->index(['event_id', 'status'], 'registrations_event_status_index');
            });
        } catch (Exception $exception) {
            // Index already exists
        }

        // Step 11: Rename event_registrations to registrations (if still old name)
        if (Schema::hasTable('event_registrations')) {
            Schema::rename('event_registrations', 'registrations');
        }

        // Step 12: Add participant_id and confirmed_at to newsletter_subscriptions
        if (! Schema::hasColumn('newsletter_subscriptions', 'participant_id')) {
            Schema::table('newsletter_subscriptions', function (Blueprint $table): void {
                $table->unsignedBigInteger('participant_id')->nullable()->after('id');
            });
        }

        if (! Schema::hasColumn('newsletter_subscriptions', 'confirmed_at')) {
            Schema::table('newsletter_subscriptions', function (Blueprint $table): void {
                $table->timestamp('confirmed_at')->nullable()->after('subscribed_at');
            });
        }

        // Step 13: Update newsletter_subscriptions with participant_id
        if (Schema::hasColumn('newsletter_subscriptions', 'email')) {
            DB::statement('
                UPDATE newsletter_subscriptions
                SET
                    participant_id = (SELECT id FROM participants WHERE participants.email = newsletter_subscriptions.email),
                    confirmed_at = subscribed_at
                WHERE participant_id IS NULL
            ');

            // Step 14: Drop old indexes and columns from newsletter_subscriptions
            try {
                Schema::table('newsletter_subscriptions', function (Blueprint $table): void {
                    $table->dropUnique(['email']);
                });
            } catch (Exception $e) {
                // Unique constraint doesn't exist
            }

            $columnsToDrop = array_filter(
                ['email', 'status'],
                fn (string $col) => Schema::hasColumn('newsletter_subscriptions', $col),
            );

            if ($columnsToDrop !== []) {
                Schema::table('newsletter_subscriptions', function (Blueprint $table) use ($columnsToDrop): void {
                    $table->dropColumn($columnsToDrop);
                });
            }
        }

        // Step 15: Add foreign key and unique constraint
        try {
            Schema::table('newsletter_subscriptions', function (Blueprint $table): void {
                $table->foreign('participant_id')->references('id')->on('participants')->cascadeOnDelete();
            });
        } catch (Exception $exception) {
            // Foreign key already exists
        }

        try {
            Schema::table('newsletter_subscriptions', function (Blueprint $table): void {
                $table->unique('participant_id');
            });
        } catch (Exception $exception) {
            // Unique constraint already exists
        }
    }

    public function down(): never
    {
        // This migration is not reversible due to data transformation
        throw new RuntimeException('This migration cannot be reversed. Please restore from backup.');
    }
};
