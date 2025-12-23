<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class ManageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected string $view = 'filament.pages.manage-settings';

    protected static ?string $title = 'Einstellungen';

    protected static ?string $navigationLabel = 'Einstellungen';

    protected static ?int $navigationSort = 100;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'site_name' => Setting::get('site_name', 'Männerkreis Niederbayern'),
            'site_tagline' => Setting::get('site_tagline', 'Ein Raum für echte Begegnung'),
            'site_description' => Setting::get('site_description', 'Der Männerkreis ist ein geschützter Ort, an dem du dich zeigen kannst, wie du wirklich bist.'),
            'contact_email' => Setting::get('contact_email', 'kontakt@mens-circle.de'),
            'contact_phone' => Setting::get('contact_phone', ''),
            'location' => Setting::get('location', 'Niederbayern'),
            'social_links' => Setting::get('social_links', []),
            'footer_text' => Setting::get('footer_text', '© '.date('Y').' Männerkreis Niederbayern. Alle Rechte vorbehalten.'),
            'google_analytics_id' => Setting::get('google_analytics_id', ''),
            'event_default_max_participants' => Setting::get('event_default_max_participants', 8),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
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

                Repeater::make('social_links')
                    ->label('Social & Kontakt Links')
                    ->schema([
                        Select::make('type')
                            ->label('Typ')
                            ->options([
                                'email' => 'E-Mail',
                                'phone' => 'Telefon',
                                'instagram' => 'Instagram',
                                'facebook' => 'Facebook',
                                'twitter' => 'Twitter (X)',
                                'linkedin' => 'LinkedIn',
                                'youtube' => 'YouTube',
                                'whatsapp' => 'WhatsApp',
                                'telegram' => 'Telegram',
                                'website' => 'Website',
                                'other' => 'Sonstiges',
                            ])
                            ->required()
                            ->searchable(),

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
                    ->itemLabel(fn (array $state): ?string => ($state['type'] ?? 'Link').' - '.($state['label'] ?? $state['value'] ?? ''))
                    ->addActionLabel('Link hinzufügen')
                    ->reorderable()
                    ->columnSpanFull(),

                Textarea::make('footer_text')
                    ->label('Footer Text')
                    ->rows(2)
                    ->helperText('Copyright-Text im Footer'),

                TextInput::make('google_analytics_id')
                    ->label('Google Analytics ID')
                    ->maxLength(255)
                    ->helperText('Optional: GA4 Measurement ID (z.B. G-XXXXXXXXXX)')
                    ->placeholder('G-XXXXXXXXXX'),

                TextInput::make('event_default_max_participants')
                    ->label('Standard Teilnehmerzahl bei Events')
                    ->numeric()
                    ->default(8)
                    ->minValue(1)
                    ->maxValue(100)
                    ->helperText('Standard-Maximalzahl für neue Events'),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Speichern')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        cache()->forget('settings');

        Notification::make()
            ->success()
            ->title('Einstellungen gespeichert')
            ->body('Die Einstellungen wurden erfolgreich aktualisiert.')
            ->send();
    }
}
