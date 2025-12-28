<?php

namespace App\Filament\Pages;

use App\Enums\Heroicon as SocialHeroicon;
use App\Enums\SocialLinkType;
use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static bool $shouldRegisterNavigation = false; // Hide from navigation

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected string $view = 'filament.pages.manage-settings';

    protected static ?string $title = 'Einstellungen (Alt - Deprecated)';

    protected static ?string $navigationLabel = null;

    protected static ?int $navigationSort = 100;

    public ?array $data = [];

    public function mount(): void
    {
        $socialLinks = Setting::get('social_links', []);

        if (! is_array($socialLinks)) {
            $socialLinks = [];
        }

        $socialLinks = collect($socialLinks)
            ->map(function (array $link): array {
                if (! empty($link['icon'])) {
                    return $link;
                }

                if (empty($link['type']) || ! is_string($link['type'])) {
                    return $link;
                }

                $heroiconName = SocialLinkType::tryFrom($link['type'])?->getHeroiconName();

                if ($heroiconName) {
                    $link['icon'] = $heroiconName;
                }

                return $link;
            })
            ->all();

        $this->form->fill([
            'site_name' => Setting::get('site_name', 'Männerkreis Niederbayern'),
            'site_tagline' => Setting::get('site_tagline', 'Ein Raum für echte Begegnung'),
            'site_description' => Setting::get('site_description', 'Der Männerkreis ist ein geschützter Ort, an dem du dich zeigen kannst, wie du wirklich bist.'),
            'contact_email' => Setting::get('contact_email', 'kontakt@mens-circle.de'),
            'contact_phone' => Setting::get('contact_phone', ''),
            'location' => Setting::get('location', 'Niederbayern'),
            'whatsapp_community_link' => Setting::get('whatsapp_community_link', ''),
            'social_links' => $socialLinks,
            'footer_text' => Setting::get('footer_text', '© '.date('Y').' Männerkreis Niederbayern. Alle Rechte vorbehalten.'),
            'event_default_max_participants' => Setting::get('event_default_max_participants', 8),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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

        Notification::make()
            ->success()
            ->title('Einstellungen gespeichert')
            ->body('Die Einstellungen wurden erfolgreich aktualisiert.')
            ->send();
    }
}
