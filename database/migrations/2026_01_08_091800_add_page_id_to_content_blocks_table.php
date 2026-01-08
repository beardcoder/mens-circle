<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * Diese Migration fügt die page_id Spalte zur content_blocks Tabelle hinzu,
     * falls sie noch nicht existiert.
     */
    public function up(): void
    {
        // Prüfe ob die content_blocks Tabelle existiert
        if (!Schema::hasTable('content_blocks')) {
            throw new Exception(
                'Die content_blocks Tabelle existiert nicht! ' .
                'Bitte führe erst die Migration 2026_01_08_064155_create_content_blocks_table aus.'
            );
        }

        // Prüfe ob page_id bereits existiert
        if (Schema::hasColumn('content_blocks', 'page_id')) {
            // Spalte existiert bereits, nichts zu tun
            return;
        }

        // Füge page_id Spalte hinzu
        Schema::table('content_blocks', function (Blueprint $table): void {
            $table->foreignId('page_id')
                ->nullable() // Erst nullable, damit bestehende Daten nicht brechen
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();

            $table->index(['page_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('content_blocks') && Schema::hasColumn('content_blocks', 'page_id')) {
            Schema::table('content_blocks', function (Blueprint $table): void {
                $table->dropForeign(['page_id']);
                $table->dropIndex(['page_id', 'order']);
                $table->dropColumn('page_id');
            });
        }
    }
};
