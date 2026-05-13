<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Health\Models\HealthCheckResultHistoryItem;
use Spatie\Health\ResultStores\EloquentHealthResultStore;

return new class extends Migration {
    public function up(): void
    {
        $connection = (new HealthCheckResultHistoryItem())->getConnectionName();
        $tableName = EloquentHealthResultStore::getHistoryItemInstance()->getTable();

        if (! Schema::connection($connection)->hasTable($tableName)) {
            Schema::connection($connection)->create($tableName, function (Blueprint $table): void {
                $table->id();

                $table->string('check_name');
                $table->string('check_label');
                $table->string('status');
                $table->text('notification_message')->nullable();
                $table->string('short_summary')->nullable();
                $table->json('meta');
                $table->timestamp('ended_at');
                $table->uuid('batch');

                $table->timestamps();
            });
        }

        Schema::connection($connection)->table($tableName, function (Blueprint $table) use ($tableName): void {
            $sm = Schema::connection($this->getConnection())->getConnection()->getSchemaBuilder();

            $indexes = collect($sm->getIndexes($tableName))->pluck('name')->toArray();

            if (! in_array($tableName . '_created_at_index', $indexes, true)) {
                $table->index('created_at');
            }

            if (! in_array($tableName . '_batch_index', $indexes, true)) {
                $table->index('batch');
            }
        });
    }

    public function getConnection(): string
    {
        return (new HealthCheckResultHistoryItem())->getConnectionName();
    }
};
