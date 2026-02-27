<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('event_notification_logs');

        Schema::table('registrations', function (Blueprint $table): void {
            $table->timestamp('reminder_sent_at')->nullable()->after('cancelled_at');
            $table->timestamp('sms_reminder_sent_at')->nullable()->after('reminder_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table): void {
            $table->dropColumn(['reminder_sent_at', 'sms_reminder_sent_at']);
        });

        Schema::create('event_notification_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('channel');
            $table->timestamp('notified_at');
            $table->timestamps();

            $table->unique(['registration_id', 'event_id', 'channel']);
        });
    }
};
