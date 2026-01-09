<?php

declare(strict_types=1);

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use App\Models\ContentBlock;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    /**
     * Load ContentBlocks into form format
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Fix: Ensure media is attached to the Page, not the ContentBlock
        $this->record->contentBlocks->each(function (ContentBlock $block) {
            $block->getMedia('page_blocks')->each(function (Media $media) {
                $media->update([
                    'model_type' => get_class($this->record),
                    'model_id' => $this->record->id,
                ]);
            });
        });

        $contentBlocks = $this->record->contentBlocks()
            ->orderBy('order')
            ->get()
            ->map(function (ContentBlock $block): array {
                $blockData = $block->data;
                $blockData['block_id'] = $block->block_id;

                return [
                    'type' => $block->type,
                    'data' => $blockData,
                ];
            })
            ->toArray();

        $data['content_blocks'] = $contentBlocks;

        return $data;
    }

    /**
     * Save ContentBlocks from form
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $contentBlocksData = $data['content_blocks'] ?? [];
        unset($data['content_blocks']);

        // Update Page without content_blocks
        $record->update($data);

        // Get existing blocks
        $existingBlocks = $record->contentBlocks->keyBy('block_id');
        $processedBlockIds = [];

        // Sync blocks (Update or Create)
        foreach ($contentBlocksData as $index => $blockData) {
            $blockContent = $blockData['data'] ?? [];
            $blockId = $blockContent['block_id'] ?? Str::uuid()->toString();
            unset($blockContent['block_id']);

            if ($existingBlock = $existingBlocks->get($blockId)) {
                $existingBlock->update([
                    'type' => $blockData['type'],
                    'data' => $blockContent,
                    'order' => $index,
                ]);
                $processedBlockIds[] = $blockId;
            } else {
                $record->contentBlocks()->create([
                    'type' => $blockData['type'],
                    'data' => $blockContent,
                    'block_id' => $blockId,
                    'order' => $index,
                ]);
                $processedBlockIds[] = $blockId;
            }
        }

        // Delete removed blocks and their media
        $existingBlocks->each(function (ContentBlock $block) use ($processedBlockIds, $record) {
            if (! in_array($block->block_id, $processedBlockIds)) {
                // Delete associated media on the Page
                Media::where('model_type', get_class($record))
                    ->where('model_id', $record->id)
                    ->where('collection_name', 'page_blocks')
                    ->where('custom_properties->block_id', $block->block_id)
                    ->get()
                    ->each(fn (Media $media) => $media->delete());

                $block->delete();
            }
        });

        return $record;
    }
}
