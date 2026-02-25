<?php

declare(strict_types=1);

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Models\ContentBlock;
use App\Models\Page;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Override;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property Page $record
 */
class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [DeleteAction::make(), ForceDeleteAction::make(), RestoreAction::make()];
    }

    /**
     * Load ContentBlocks into form format
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->record->contentBlocks->each(function ($blockItem): void {
            /** @var ContentBlock $blockItem */
            $blockItem
                ->getMedia('page_blocks')
                ->each(function ($mediaItem): void {
                    /** @var Media $mediaItem */
                    $mediaItem->update([
                        'model_type' => \get_class($this->record),
                        'model_id' => $this->record->id,
                    ]);
                });
        });

        $contentBlocks = $this->record
            ->contentBlocks()
            ->orderBy('order')
            ->get()
            ->map(static function ($blockItem): array {
                /** @var ContentBlock $blockItem */
                /** @var array<string, mixed> $blockData */
                $blockData = $blockItem->data;
                $blockData['block_id'] = $blockItem->block_id;

                return [
                    'type' => $blockItem->type,
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
        /** @var array<int, array<string, mixed>> $contentBlocksData */
        $contentBlocksData = $data['content_blocks'] ?? [];
        unset($data['content_blocks']);

        $record->update($data);

        /** @var Page $record */
        $record->saveContentBlocks($contentBlocksData);

        return $record;
    }
}
