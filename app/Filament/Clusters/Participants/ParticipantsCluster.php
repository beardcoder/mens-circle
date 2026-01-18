<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Participants;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class ParticipantsCluster extends Cluster
{
    protected static ?string $navigationLabel = 'Teilnehmer';

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?int $navigationSort = 30;
}
