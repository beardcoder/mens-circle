<?php

use App\Models\ContentBlock;
use App\Models\Page;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * WICHTIG: Diese Migration sollte erst ausgeführt werden, wenn:
     * 1. Die ContentBlock-Migration erfolgreich durchgelaufen ist
     * 2. Die Anwendung im Produktivbetrieb stabil läuft
     * 3. Ein Backup der Datenbank existiert
     * 4. Du verifiziert hast, dass alle Daten korrekt migriert wurden
     */
    public function up(): void
    {
        // Prüfe ob die content_blocks Spalte überhaupt existiert
        if (!Schema::hasColumn('pages', 'content_blocks')) {
            // Spalte existiert bereits nicht mehr, nichts zu tun
            return;
        }

        // Prüfe ob die content_blocks Tabelle existiert und die page_id Spalte hat
        if (!Schema::hasTable('content_blocks') || !Schema::hasColumn('content_blocks', 'page_id')) {
            throw new \Exception(
                "Die content_blocks Tabelle oder page_id Spalte existiert nicht! " .
                "Bitte führe erst die vorherigen Migrationen aus."
            );
        }

        // Sicherheits-Check: Validiere dass alle Pages ContentBlocks haben
        $pagesWithContentBlocks = Page::whereHas('contentBlocks')->count();
        $totalPages = Page::count();

        if ($pagesWithContentBlocks < $totalPages) {
            throw new \Exception(
                "WARNUNG: Nicht alle Pages haben ContentBlocks! " .
                "({$pagesWithContentBlocks}/{$totalPages}). " .
                "Bitte überprüfe die Datenmigration bevor du die Spalte löschst."
            );
        }

        // Lösche die content_blocks Spalte
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('content_blocks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->jsonb('content_blocks')->nullable()->after('slug');
        });

        // Optional: Migriere Daten zurück
        // (nur wenn die vorherige Migration noch nicht zurückgerollt wurde)
    }
};
