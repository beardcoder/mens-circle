<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Content;

use Filament\Clusters\Cluster;

class ContentCluster extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Inhalte';

    protected static ?int $navigationSort = 30;
}
