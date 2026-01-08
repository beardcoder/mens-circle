<?php

use App\Models\ContentBlock;
use App\Models\Page;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migriere bestehende content_blocks JSON Daten zu ContentBlock Models
        DB::table('pages')
            ->whereNotNull('content_blocks')
            ->orderBy('id')
            ->each(function ($page) {
                // Dekodiere JSON-String
                $contentBlocks = json_decode($page->content_blocks, true);

                if (empty($contentBlocks) || !is_array($contentBlocks)) {
                    return;
                }

                foreach ($contentBlocks as $index => $block) {
                    $blockData = $block['data'] ?? [];
                    $blockId = $blockData['block_id'] ?? null;

                    // Entferne block_id aus den Daten, da es jetzt ein eigenes Feld ist
                    unset($blockData['block_id']);

                    $contentBlock = ContentBlock::create([
                        'contentable_type' => Page::class,
                        'contentable_id' => $page->id,
                        'type' => $block['type'],
                        'data' => $blockData,
                        'block_id' => $blockId ?? \Illuminate\Support\Str::uuid(),
                        'order' => $index,
                    ]);

                    // Migriere Media Library Zuordnungen
                    if ($blockId) {
                        Media::where('model_type', Page::class)
                            ->where('model_id', $page->id)
                            ->where('collection_name', 'page_blocks')
                            ->where('custom_properties->block_id', $blockId)
                            ->update([
                                'model_type' => ContentBlock::class,
                                'model_id' => $contentBlock->id,
                            ]);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Migriere ContentBlock Models zurück zu JSON
        DB::table('pages')->orderBy('id')->each(function ($page) {
            $contentBlocks = [];

            $blocks = ContentBlock::where('contentable_type', '=', Page::class)
                ->where('contentable_id', '=', $page->id)
                ->orderBy('order')
                ->get();

            foreach ($blocks as $block) {
                $data = $block->data;
                $data['block_id'] = $block->block_id;

                $contentBlocks[] = [
                    'type' => $block->type,
                    'data' => $data,
                ];

                // Migriere Media Library Zuordnungen zurück
                Media::where('model_type', ContentBlock::class)
                    ->where('model_id', $block->id)
                    ->where('collection_name', 'page_blocks')
                    ->update([
                        'model_type' => Page::class,
                        'model_id' => $page->id,
                    ]);
            }

            // Update als JSON-String
            DB::table('pages')
                ->where('id', $page->id)
                ->update(['content_blocks' => json_encode($contentBlocks)]);

            // Lösche ContentBlock Einträge
            ContentBlock::where('contentable_type', '=', Page::class)
                ->where('contentable_id', '=', $page->id)
                ->delete();
        });
    }
};
