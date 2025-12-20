<?php

namespace App\Filament\Pages;

use App\Jobs\SendNewsletterJob;
use App\Models\Newsletter;
use App\Models\NewsletterSubscription;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
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

    public function form(Form $form): Form
    {
        return $form
            ->schema([
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
                ->modalDescription(function () {
                    $count = NewsletterSubscription::where('status', 'active')->count();
                    return "Der Newsletter wird an {$count} aktive Abonnenten versendet. Dies kann nicht rückgängig gemacht werden.";
                })
                ->modalSubmitActionLabel('Jetzt versenden')
                ->action(function () {
                    $data = $this->form->getState();

                    $newsletter = Newsletter::create([
                        'subject' => $data['subject'],
                        'content' => $data['content'],
                        'status' => 'draft',
                    ]);

                    SendNewsletterJob::dispatch($newsletter);

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
