<?php

declare(strict_types=1);

namespace App\Filament\Resources\EventResource\Pages;

use App\Actions\SendMessageToEventParticipants;
use App\Enums\EmailTemplate;
use App\Enums\MessengerTemplate;
use App\Filament\Resources\EventResource;
use App\Models\Event;
use App\Services\EmailTemplateService;
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
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\View\View;
use Override;

final class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            $this->messengerTextsAction(),
            $this->sendMessageAction(),
            EventResource::replicateAction(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    private function messengerTextsAction(): Action
    {
        return Action::make('messengerTexts')
            ->label('Messenger-Texte')
            ->icon(Heroicon::OutlinedChatBubbleLeftRight)
            ->color('info')
            ->modalHeading('Messenger-Texte')
            ->modalDescription('Fertige Texte für WhatsApp, Signal, Telegram & Co. – direkt kopieren und verschicken.')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Schließen')
            ->modalWidth('3xl')
            ->modalContent(function (): View {
                /** @var Event $event */
                $event = $this->record;
                $service = app(EmailTemplateService::class);

                $variants = [];
                foreach (MessengerTemplate::availableForSpots($event->availableSpots) as $template) {
                    $variants[$template->value] = [
                        'label' => $template->getLabel(),
                        'text' => $service->renderForMessenger($template->getContent(), $event),
                    ];
                }

                return view('filament.components.messenger-texts', ['variants' => $variants]);
            });
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
                            ->options(EmailTemplate::participantTemplateOptions())
                            ->placeholder('Vorlage auswählen (optional)')
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (?string $state, Set $set): void {
                                if (!$state) {
                                    return;
                                }

                                /** @var Event $event */
                                $event = $this->record;
                                $resolved = app(EmailTemplateService::class)->resolve(EmailTemplate::from($state), $event);

                                $set('subject', $resolved['subject']);
                                $set('content', $resolved['content']);
                            }),

                        TextEntry::make('placeholders_info')
                            ->label('Verfügbare Platzhalter')
                            ->state(implode(', ', EmailTemplate::placeholders())),
                    ])
                    ->collapsible(),

                TextInput::make('subject')->label('Betreff')->required()->maxLength(255)->placeholder('Betreff der Nachricht'),

                RichEditor::make('content')
                    ->label('Nachricht')
                    ->required()
                    ->toolbarButtons(['bold', 'italic', 'link', 'h2', 'h3', 'bulletList', 'orderedList', 'undo', 'redo'])
                    ->placeholder('Nachricht an die Teilnehmer...'),
            ])
            ->modalHeading('Nachricht an angemeldete Teilnehmer')
            ->modalDescription(function (): string {
                /** @var Event $event */
                $event = $this->record;

                return (
                    'Die Nachricht wird an '
                    . $event->activeRegistrations()->count()
                    . ' angemeldete Teilnehmer von „'
                    . $event->title
                    . '" gesendet.'
                );
            })
            ->modalSubmitActionLabel('Nachricht senden')
            ->action(function (array $data, SendMessageToEventParticipants $sender): void {
                /** @var Event $event */
                $event = $this->record;

                $result = $sender->execute($event, $data['subject'], $data['content']);

                $body = "Nachricht wurde an {$result['sent']} Teilnehmer gesendet.";
                if ($result['failed'] > 0) {
                    $body .= " {$result['failed']} Zustellungen fehlgeschlagen.";
                }

                Notification::make()->title('Nachricht versendet')->body($body)->success()->send();
            });
    }
}
