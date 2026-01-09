<?php

declare(strict_types=1);

namespace App\Filament\Resources\EventRegistrations;

use App\Filament\Resources\EventRegistrations\Pages\CreateEventRegistration;
use App\Filament\Resources\EventRegistrations\Pages\EditEventRegistration;
use App\Filament\Resources\EventRegistrations\Pages\ListEventRegistrations;
use App\Models\EventRegistration;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EventRegistrationResource extends Resource
{
    protected static ?string $model = EventRegistration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('event_id')
                    ->label('Veranstaltung')
                    ->relationship('event', 'title')
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('first_name')
                    ->label('Vorname')
                    ->required()
                    ->maxLength(255),
                TextInput::make('last_name')
                    ->label('Nachname')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('E-Mail')
                    ->email()
                    ->required()
                    ->maxLength(255),
                TextInput::make('phone_number')
                    ->label('Telefonnummer')
                    ->tel()
                    ->maxLength(30),
                Toggle::make('privacy_accepted')
                    ->label('Datenschutz akzeptiert')
                    ->default(false),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'confirmed' => 'Bestätigt',
                        'cancelled' => 'Abgesagt',
                        'waitlist' => 'Warteliste',
                    ])
                    ->required()
                    ->default('confirmed'),
                DateTimePicker::make('confirmed_at')
                    ->label('Bestätigt am')
                    ->native(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('event.title')
                    ->label('Veranstaltung')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('first_name')
                    ->label('Vorname')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('last_name')
                    ->label('Nachname')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('E-Mail')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('phone_number')
                    ->label('Telefon')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'confirmed' => 'success',
                        'cancelled' => 'danger',
                        'waitlist' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'confirmed' => 'Bestätigt',
                        'cancelled' => 'Abgesagt',
                        'waitlist' => 'Warteliste',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Angemeldet am')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Aktualisiert')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'confirmed' => 'Bestätigt',
                        'cancelled' => 'Abgesagt',
                        'waitlist' => 'Warteliste',
                    ]),
                SelectFilter::make('event')
                    ->label('Veranstaltung')
                    ->relationship('event', 'title')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEventRegistrations::route('/'),
            'create' => CreateEventRegistration::route('/create'),
            'edit' => EditEventRegistration::route('/{record}/edit'),
        ];
    }
}
