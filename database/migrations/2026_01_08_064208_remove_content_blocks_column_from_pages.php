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
        // WICHTIG: Diese Spalte NICHT sofort in Produktion löschen!
        // Erst nach erfolgreicher Migration und Test-Phase manuell ausführen:
        // Schema::table('pages', function (Blueprint $table) {
        //     $table->dropColumn('content_blocks');
        // });

        // Für lokale Entwicklung kannst du das Löschen aktivieren,
        // aber in Produktion solltest du die Spalte als Backup behalten.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table): void {
            $table->jsonb('content_blocks')->nullable()->after('slug');
        });
    }
};
