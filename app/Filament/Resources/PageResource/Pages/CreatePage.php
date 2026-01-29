<?php

declare(strict_types=1);

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Models\Page;
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
        /** @var array<int, array<string, mixed>> $contentBlocksData */
        $contentBlocksData = $data['content_blocks'] ?? [];
        unset($data['content_blocks']);

        /** @var Page $record */
        $record = static::getModel()::create($data);

        $record->saveContentBlocks($contentBlocksData);

        return $record;
    }
}
