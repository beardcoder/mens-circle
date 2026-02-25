<?php

declare(strict_types=1);

namespace App\Filament\Resources\EventResource\Pages;

use App\Enums\EmailTemplate;
use App\Enums\RegistrationStatus;
use App\Filament\Resources\EventResource;
use App\Mail\EventParticipantMessage;
use App\Models\Event;
use App\Services\EmailTemplateService;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Override;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            $this->sendMessageAction(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    private function sendMessageAction(): Action
    {
        return Action::make('sendMessage')
            ->label('Nachricht senden')
            ->icon(Heroicon::OutlinedEnvelope)
            ->color('warning')
            ->schema([
                Section::make('Vorlage')
                    ->description('Wähle eine Vorlage oder schreibe eine eigene Nachricht')
                    ->schema([
                        Select::make('template')
                            ->label('E-Mail-Vorlage')
                            ->options(
                                collect(EmailTemplate::participantTemplates())
                                    ->mapWithKeys(static fn(EmailTemplate $template): array => [
                                        $template->value => $template->getLabel(),
                                    ])->all(),
                            )
                            ->placeholder('Vorlage auswählen (optional)')
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (?string $state, callable $set): void {
                                if (!$state) {
                                    return;
                                }

                                /** @var Event $event */
                                $event = $this->record;
                                $template = EmailTemplate::from($state);
                                $service = new EmailTemplateService();
                                $resolved = $service->resolve($template, $event);

                                $set('subject', $resolved['subject']);
                                $set('content', $resolved['content']);
                            }),

                        TextEntry::make('placeholders_info')
                            ->label('Verfügbare Platzhalter')
                            ->state(implode(', ', EmailTemplate::placeholders())),
                    ])
                    ->collapsible(),

                TextInput::make('subject')
                    ->label('Betreff')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Betreff der Nachricht'),

                RichEditor::make('content')
                    ->label('Nachricht')
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
                    ->placeholder('Nachricht an die Teilnehmer...'),
            ])
            ->modalHeading('Nachricht an angemeldete Teilnehmer')
            ->modalDescription(function (): string {
                /** @var Event $event */
                $event = $this->record;
                $count = $event
                    ->registrations()
                    ->whereIn('status', [RegistrationStatus::Registered, RegistrationStatus::Attended])
                    ->count();

                return 'Die Nachricht wird an ' . $count . ' angemeldete Teilnehmer von „' . $event->title . '" gesendet.';
            })
            ->modalSubmitActionLabel('Nachricht senden')
            ->action(function (array $data): void {
                /** @var Event $event */
                $event = $this->record;

                $registrations = $event
                    ->registrations()
                    ->whereIn('status', [RegistrationStatus::Registered, RegistrationStatus::Attended])
                    ->with('participant')
                    ->get();

                $sentCount = 0;
                $failedCount = 0;

                foreach ($registrations as $registration) {
                    try {
                        Mail::to($registration->participant->email)->send(new EventParticipantMessage(
                            mailSubject: $data['subject'],
                            mailContent: $data['content'],
                            event: $event,
                            participantName: $registration->participant->first_name,
                        ));
                        $sentCount++;
                    } catch (Exception $exception) {
                        Log::error('Failed to send participant message', [
                            'event_id' => $event->id,
                            'participant_id' => $registration->participant->id,
                            'error' => $exception->getMessage(),
                        ]);
                        $failedCount++;
                    }
                }

                $body = "Nachricht wurde an {$sentCount} Teilnehmer gesendet.";
                if ($failedCount > 0) {
                    $body .= " {$failedCount} Zustellungen fehlgeschlagen.";
                }

                Notification::make()
                    ->title('Nachricht versendet')
                    ->body($body)
                    ->success()
                    ->send();
            });
    }
}
