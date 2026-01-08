<?php

use App\Models\ContentBlock;
use App\Models\Page;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migriere bestehende content_blocks JSON Daten zu ContentBlock Models
        Page::whereNotNull('content_blocks')
            ->orderBy('id')
            ->each(function (Page $page) {
                $contentBlocks = is_string($page->content_blocks)
                    ? json_decode($page->content_blocks, true)
                    : $page->content_blocks;

                if (empty($contentBlocks) || ! is_array($contentBlocks)) {
                    return;
                }

                foreach ($contentBlocks as $index => $block) {
                    $blockData = $block['data'] ?? [];
                    $blockId = $blockData['block_id'] ?? null;

                    // Entferne block_id aus den Daten, da es jetzt ein eigenes Feld ist
                    unset($blockData['block_id']);

                    $contentBlock = ContentBlock::create([
                        'page_id' => $page->id,
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
        Page::orderBy('id')->each(function (Page $page) {
            $pageId = $page->id;

            $contentBlocks = $page->contentBlocks->map(function (ContentBlock $block) use ($pageId) {
                $data = $block->data;
                $data['block_id'] = $block->block_id;

                // Migriere Media Library Zuordnungen zurück
                Media::where('model_type', ContentBlock::class)
                    ->where('model_id', $block->id)
                    ->where('collection_name', 'page_blocks')
                    ->update([
                        'model_type' => Page::class,
                        'model_id' => $pageId,
                    ]);

                return [
                    'type' => $block->type,
                    'data' => $data,
                ];
            })->toArray();

            // Update als JSON via DB facade (content_blocks ist nicht mehr in fillable)
            DB::table('pages')
                ->where('id', $pageId)
                ->update(['content_blocks' => json_encode($contentBlocks)]);

            // Lösche ContentBlock Einträge
            $page->contentBlocks()->delete();
        });
    }
};
