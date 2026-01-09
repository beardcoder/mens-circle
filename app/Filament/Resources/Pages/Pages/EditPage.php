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
     * Lade ContentBlocks in das Formular-Format
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
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
     * Speichere ContentBlocks aus dem Formular
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $contentBlocksData = $data['content_blocks'] ?? [];
        unset($data['content_blocks']);

        // Update Page ohne content_blocks
        $record->update($data);

        // Lösche alte ContentBlocks
        $record->contentBlocks()->delete();

        // Erstelle neue ContentBlocks
        foreach ($contentBlocksData as $index => $blockData) {
            $data = $blockData['data'] ?? [];
            $blockId = $data['block_id'] ?? Str::uuid();
            unset($data['block_id']);

            $contentBlock = $record->contentBlocks()->create([
                'type' => $blockData['type'],
                'data' => $data,
                'block_id' => $blockId,
                'order' => $index,
            ]);

            Media::where('model_type', get_class($record))
                ->where('model_id', $record->id)
                ->where('collection_name', 'page_blocks')
                ->where('custom_properties->block_id', $blockId)
                ->update([
                    'model_type' => ContentBlock::class,
                    'model_id' => $contentBlock->id,
                ]);
        }

        return $record;
    }
}
