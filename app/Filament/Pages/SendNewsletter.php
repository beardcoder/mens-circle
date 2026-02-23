<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\EmailTemplate;
use App\Enums\NewsletterStatus;
use App\Jobs\SendNewsletterJob;
use App\Models\Event;
use App\Models\Newsletter;
use App\Models\NewsletterSubscription;
use App\Services\EmailTemplateService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;

class SendNewsletter extends Page implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    protected string $view = 'filament.pages.send-newsletter';

    protected static ?string $navigationLabel = 'Newsletter versenden';

    protected static ?string $title = 'Newsletter versenden';

    protected static ?int $navigationSort = 70;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        /** @phpstan-ignore property.notFound */
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        $nextEvent = Event::nextEvent();

        return $schema
            ->components([
                Section::make('Vorlage')
                    ->description('Wähle eine Vorlage, um Betreff und Inhalt automatisch auszufüllen')
                    ->schema([
                        Select::make('template')
                            ->label('E-Mail-Vorlage')
                            ->options(
                                collect(EmailTemplate::newsletterTemplates())
                                    ->mapWithKeys(fn (EmailTemplate $template): array => [
$template->value => $template->getLabel()
])
                                    ->all(),
                            )
                            ->placeholder('Vorlage auswählen (optional)')
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (?string $state, callable $set): void {
                                if (!$state) {
                                    return;
                                }

                                $template = EmailTemplate::from($state);
                                $service = new EmailTemplateService();
                                $resolved = $service->resolve($template);

                                $set('subject', $resolved['subject']);
                                $set('content', $resolved['content']);
                            })
                            ->helperText($nextEvent
                                ? "Platzhalter werden mit Daten vom nächsten Event gefüllt: {$nextEvent->title} ({$nextEvent->event_date->translatedFormat(
                                    'd. F Y'
                                )})"
                                : 'Kein kommendes Event vorhanden – Platzhalter werden mit „—" gefüllt'),

                        TextEntry::make('placeholders_info')
                            ->label('Verfügbare Platzhalter')
                            ->state(implode(', ', EmailTemplate::placeholders()))
                            ->helperText(
                                'Diese Platzhalter können im Betreff und Inhalt verwendet werden und werden beim Auswählen einer Vorlage automatisch ersetzt'
                            ),
                    ])
                    ->collapsible(),

                TextInput::make('subject')
                    ->label('Betreff')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('z.B. Nächstes Treffen am 24. Januar')
                    ->helperText('Der Betreff erscheint in der E-Mail-Vorschau'),

                RichEditor::make('content')
                    ->label('Newsletter-Inhalt')
                    ->required()
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'link',
                        'h2',
                        'h3',
                        'bulletList',
                        'orderedList',
                        'undo',
                        'redo',
                    ])
                    ->placeholder('Schreibe deine Newsletter-Nachricht hier...')
                    ->helperText('Formatiere deinen Text mit den Werkzeugen oben'),
            ])
            ->statePath('data');
    }

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('sendNewsletter')
                ->label('Newsletter versenden')
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Newsletter versenden?')
                ->modalDescription(function (): string {
                    $count = NewsletterSubscription::whereNull('unsubscribed_at')->count();

                    return "Der Newsletter wird an {$count} aktive Abonnenten versendet. Dies kann nicht rückgängig gemacht werden.";
                })
                ->modalSubmitActionLabel('Jetzt versenden')
                ->action(fn () => $this->sendNewsletterAction()),
        ];
    }

    public function sendNewsletterAction(): void
    {
        /** @phpstan-ignore property.notFound */
        $data = $this->form->getState();

        $newsletter = Newsletter::create([
            'subject' => $data['subject'],
            'content' => $data['content'],
            'status' => NewsletterStatus::Draft,
        ]);

        dispatch(new SendNewsletterJob($newsletter));

        Notification::make()
            ->title('Newsletter wird versendet')
            ->body('Der Newsletter wird im Hintergrund an alle Abonnenten versendet.')
            ->success()
            ->send();

        /** @phpstan-ignore property.notFound */
        $this->form->fill();
    }
}
