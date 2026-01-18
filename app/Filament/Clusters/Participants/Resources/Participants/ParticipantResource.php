<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Participants\Resources\Participants;

use App\Filament\Clusters\Participants\ParticipantsCluster;
use App\Filament\Clusters\Participants\Resources\Participants\Pages\CreateParticipant;
use App\Filament\Clusters\Participants\Resources\Participants\Pages\EditParticipant;
use App\Filament\Clusters\Participants\Resources\Participants\Pages\ListParticipants;
use App\Filament\Clusters\Participants\Resources\Participants\Schemas\ParticipantForm;
use App\Filament\Clusters\Participants\Resources\Participants\Tables\ParticipantTable;
use App\Models\Participant;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ParticipantResource extends Resource
{
    protected static ?string $model = Participant::class;

    protected static ?string $cluster = ParticipantsCluster::class;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedUser;

    protected static ?string $navigationLabel = 'Alle Teilnehmer';

    protected static ?string $modelLabel = 'Teilnehmer';

    protected static ?string $pluralModelLabel = 'Teilnehmer';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return ParticipantForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ParticipantTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListParticipants::route('/'),
            'create' => CreateParticipant::route('/create'),
            'edit' => EditParticipant::route('/{record}/edit'),
        ];
    }
}
