<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Events;

use BackedEnum;
use Filament\Clusters\Cluster;

class EventsCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Veranstaltungen';

    protected static ?int $navigationSort = 10;
}
