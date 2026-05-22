<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('navigation_items', static function (Blueprint $table): void {
            $table->id();
            $table->string('location')->index();
            $table->string('label');
            $table->string('url')->default('');
            $table->string('condition')->nullable();
            $table->boolean('open_in_new_tab')->default(false);
            $table->boolean('is_cta')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->string('umami_event_target')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('navigation_items');
    }
};
