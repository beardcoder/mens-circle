<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
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

    public function down(): void
    {
        Schema::dropIfExists('event_notification_logs');
    }
};
