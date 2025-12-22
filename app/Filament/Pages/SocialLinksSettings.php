<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SocialLinksSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedShare;

    protected string $view = 'filament.pages.social-links-settings';

    protected static ?string $navigationLabel = 'Social Media & Kontakt';

    protected static ?string $title = 'Social Media & Kontakt Einstellungen';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'website_url' => Setting::get('website_url', config('app.url')),
            'whatsapp_url' => Setting::get('whatsapp_url', ''),
            'github_url' => Setting::get('github_url', ''),
            'contact_email' => Setting::get('contact_email', config('mail.from.address')),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Social Media Links')
                    ->description('Konfiguriere die Social Media Links, die im Footer und in E-Mails angezeigt werden.')
                    ->schema([
                        TextInput::make('website_url')
                            ->label('Webseiten-URL')
                            ->url()
                            ->placeholder('https://mens-circle.de')
                            ->helperText('Vollständige URL zur Webseite')
                            ->prefixIcon(Heroicon::OutlineGlobeAlt)
                            ->maxLength(255),

                        TextInput::make('whatsapp_url')
                            ->label('WhatsApp-Link')
                            ->url()
                            ->placeholder('https://wa.me/491234567890')
                            ->helperText('WhatsApp Chat-Link (z.B. https://wa.me/491234567890)')
                            ->prefixIcon('heroicon-o-device-phone-mobile')
                            ->maxLength(255),

                        TextInput::make('github_url')
                            ->label('GitHub-Link')
                            ->url()
                            ->placeholder('https://github.com/username/repo')
                            ->helperText('Link zum GitHub Repository')
                            ->prefixIcon('heroicon-o-code-bracket')
                            ->maxLength(255),
                    ]),

                Section::make('Kontaktinformationen')
                    ->description('Kontaktdaten, die auf der Webseite und in E-Mails angezeigt werden.')
                    ->schema([
                        TextInput::make('contact_email')
                            ->label('Kontakt E-Mail')
                            ->email()
                            ->placeholder('info@mens-circle.de')
                            ->helperText('E-Mail-Adresse für Kontaktanfragen')
                            ->prefixIcon(Heroicon::OutlineEnvelope)
                            ->maxLength(255),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Einstellungen speichern')
                ->icon(Heroicon::OutlineCheckCircle)
                ->color('primary')
                ->action(function () {
                    $data = $this->form->getState();

                    Setting::set('website_url', $data['website_url'] ?? '');
                    Setting::set('whatsapp_url', $data['whatsapp_url'] ?? '');
                    Setting::set('github_url', $data['github_url'] ?? '');
                    Setting::set('contact_email', $data['contact_email'] ?? '');

                    Notification::make()
                        ->title('Einstellungen gespeichert')
                        ->body('Die Social Media und Kontakt-Einstellungen wurden erfolgreich aktualisiert.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
