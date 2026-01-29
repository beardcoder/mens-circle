<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('event_registrations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->boolean('privacy_accepted')->default(false);
            $table->string('status')->default('confirmed');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
            $table->string('phone_number')->nullable();

            $table->unique(['event_id', 'email']);
            $table->index(['event_id', 'status'], 'event_registrations_event_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
    }
};
