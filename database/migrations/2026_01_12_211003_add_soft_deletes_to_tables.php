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
        Schema::table('pages', function (Blueprint $table): void {
            $table->softDeletes();
        });

        Schema::table('testimonials', function (Blueprint $table): void {
            $table->softDeletes();
        });

        Schema::table('events', function (Blueprint $table): void {
            $table->softDeletes();
        });

        Schema::table('newsletter_subscriptions', function (Blueprint $table): void {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });

        Schema::table('testimonials', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });

        Schema::table('events', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });

        Schema::table('newsletter_subscriptions', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });
    }
};
