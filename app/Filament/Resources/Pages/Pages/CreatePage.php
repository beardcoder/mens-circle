<?php

declare(strict_types=1);

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;

    /**
     * Save ContentBlocks after Page creation
     */
    protected function handleRecordCreation(array $data): Model
    {
        $contentBlocksData = $data['content_blocks'] ?? [];
        unset($data['content_blocks']);

        // Create Page without content_blocks
        $record = static::getModel()::create($data);

        // Sync ContentBlocks
        $record->saveContentBlocks($contentBlocksData);

        return $record;
    }
}
