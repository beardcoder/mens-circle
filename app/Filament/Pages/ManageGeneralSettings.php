<?php

namespace App\Filament\Pages;

use App\Enums\Heroicon as SocialHeroicon;
use App\Settings\GeneralSettings;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;

class ManageGeneralSettings extends SettingsPage
{
    protected static string $settings = GeneralSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $title = 'Einstellungen';

    protected static ?string $navigationLabel = 'Einstellungen';

    protected static ?int $navigationSort = 100;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Website-Informationen')
                    ->description('Grundlegende Informationen über die Website')
                    ->schema([
                        TextInput::make('site_name')
                            ->label('Seitenname')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Der Name der Website (z.B. "Männerkreis Niederbayern")'),

                        TextInput::make('site_tagline')
                            ->label('Tagline')
                            ->maxLength(255)
                            ->helperText('Kurze Beschreibung der Website'),

                        Textarea::make('site_description')
                            ->label('Seitenbeschreibung')
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText('Wird für SEO Meta Tags verwendet'),
                    ])
                    ->columns(1),

                Section::make('Kontaktinformationen')
                    ->description('Kontaktdaten für Besucher')
                    ->schema([
                        TextInput::make('contact_email')
                            ->label('Kontakt E-Mail')
                            ->email()
                            ->required()
                            ->helperText('Hauptkontakt-E-Mail-Adresse'),

                        TextInput::make('contact_phone')
                            ->label('Telefonnummer')
                            ->tel()
                            ->helperText('Optional: Telefonnummer für Kontakt'),

                        TextInput::make('location')
                            ->label('Standort')
                            ->maxLength(255)
                            ->helperText('Hauptstandort (z.B. "Niederbayern", "Straubing")'),
                    ])
                    ->columns(1),

                Section::make('Community & Social Media')
                    ->description('WhatsApp und Social Media Links')
                    ->schema([
                        TextInput::make('whatsapp_community_link')
                            ->label('WhatsApp Community Link')
                            ->url()
                            ->maxLength(500)
                            ->placeholder('https://chat.whatsapp.com/...')
                            ->helperText('Einladungslink zur WhatsApp Community. Leer lassen um die Sektion auszublenden.'),

                        Repeater::make('social_links')
                            ->label('Social & Kontakt Links')
                            ->schema([
                                Select::make('icon')
                                    ->label('Heroicon')
                                    ->options(SocialHeroicon::options())
                                    ->required()
                                    ->searchable()
                                    ->helperText('Wähle ein Heroicon für den Link.'),

                                TextInput::make('label')
                                    ->label('Beschriftung')
                                    ->maxLength(255)
                                    ->helperText('Wird als Tooltip/Alt-Text angezeigt'),

                                TextInput::make('value')
                                    ->label('Wert (URL/E-Mail/Telefon)')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Komplette URL oder E-Mail/Tel-Nummer'),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->itemLabel(function (array $state): string {
                                $iconLabel = null;

                                if (! empty($state['icon'])) {
                                    $iconLabel = SocialHeroicon::fromName($state['icon'])?->getLabel();
                                } elseif (! empty($state['type']) && is_string($state['type'])) {
                                    $iconLabel = ucwords(str_replace(['-', '_'], ' ', $state['type']));
                                }

                                $detail = $state['label'] ?? $state['value'] ?? null;

                                if ($detail) {
                                    return ($iconLabel ?? 'Link').' - '.$detail;
                                }

                                return $iconLabel ?? 'Link';
                            })
                            ->addActionLabel('Link hinzufügen')
                            ->reorderable()
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Footer & Events')
                    ->description('Footer-Text und Event-Einstellungen')
                    ->schema([
                        Textarea::make('footer_text')
                            ->label('Footer Text')
                            ->rows(2)
                            ->helperText('Copyright-Text im Footer'),

                        TextInput::make('event_default_max_participants')
                            ->label('Standard Teilnehmerzahl bei Events')
                            ->numeric()
                            ->default(8)
                            ->minValue(1)
                            ->maxValue(100)
                            ->helperText('Standard-Maximalzahl für neue Events'),
                    ])
                    ->columns(1),
            ]);
    }
}
