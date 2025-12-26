<?php

namespace App\Filament\Pages;

use App\Models\Newsletter;
use App\Models\NewsletterSubscription;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SendNewsletter extends Page implements HasForms
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    protected string $view = 'filament.pages.send-newsletter';

    protected static ?string $navigationLabel = 'Newsletter versenden';

    protected static ?string $title = 'Newsletter versenden';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('subject')
                    ->label('Betreff')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('z.B. Nächstes Treffen am 24. Januar'),

                Textarea::make('content')
                    ->label('Nachricht')
                    ->required()
                    ->rows(10)
                    ->placeholder('Schreibe deine Newsletter-Nachricht hier...'),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('send')
                ->label('Newsletter versenden')
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Newsletter versenden?')
                ->modalDescription(function (): string {
                    $count = NewsletterSubscription::where('status', 'active')->count();

                    return sprintf('Der Newsletter wird an %s aktive Abonnenten versendet. Dies kann nicht rückgängig gemacht werden.', $count);
                })
                ->modalSubmitActionLabel('Jetzt versenden')
                ->action(function (): void {
                    $data = $this->form->getState();

                    $newsletter = Newsletter::create([
                        'subject' => $data['subject'],
                        'content' => $data['content'],
                        'status' => 'draft',
                    ]);

                    dispatch(new \App\Jobs\SendNewsletterJob($newsletter));

                    Notification::make()
                        ->title('Newsletter wird versendet')
                        ->body('Der Newsletter wird im Hintergrund an alle Abonnenten versendet.')
                        ->success()
                        ->send();

                    $this->form->fill();
                }),
        ];
    }
}
