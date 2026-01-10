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

        // Sync ContentBlocks via Model method
        $record->saveContentBlocks($contentBlocksData);

        return $record;
    }
}
