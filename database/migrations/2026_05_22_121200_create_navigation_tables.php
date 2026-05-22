<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('navigations', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('type'); // header, footer, legal
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'is_active', 'deleted_at']);
        });

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            DB::statement('CREATE UNIQUE INDEX navigations_unique_active_type ON navigations (type) WHERE deleted_at IS NULL AND is_active = 1');
        }

        Schema::create('navigation_items', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('navigation_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('parent_id')->nullable()->constrained('navigation_items')->onDelete('cascade');
            $table->string('label');
            $table->string('url')->nullable();
            $table->string('route_name')->nullable();
            $table->json('route_params')->nullable();
            $table->string('anchor')->nullable(); // For #anchor links
            $table->string('target')->nullable()->default('_self'); // _self, _blank
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('icon')->nullable();
            $table->string('css_class')->nullable();
            $table->json('data_attributes')->nullable(); // For data-umami-event etc
            $table->timestamps();
            $table->softDeletes();

            $table->index(['navigation_id', 'order']);
            $table->index(['parent_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('navigation_items');
        Schema::dropIfExists('navigations');
    }
};
