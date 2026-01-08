<?php

declare(strict_types=1);

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use App\Models\ContentBlock;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;

    /**
     * Speichere ContentBlocks nach dem Erstellen der Page
     */
    protected function handleRecordCreation(array $data): Model
    {
        $contentBlocksData = $data['content_blocks'] ?? [];
        unset($data['content_blocks']);

        // Erstelle Page ohne content_blocks
        $record = static::getModel()::create($data);

        // Erstelle ContentBlocks
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

            // Migriere Media Library Zuordnungen falls vorhanden
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
