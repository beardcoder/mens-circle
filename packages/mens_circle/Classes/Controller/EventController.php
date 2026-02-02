<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Controller;

use BeardCoder\MensCircle\Domain\Model\Event;
use BeardCoder\MensCircle\Domain\Repository\EventRepository;
use BeardCoder\MensCircle\Service\RegistrationService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

final class EventController extends ActionController
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly RegistrationService $registrationService,
    ) {
    }

    public function listAction(): ResponseInterface
    {
        $events = $this->eventRepository->findUpcoming();
        $this->view->assign('events', $events);

        return $this->htmlResponse();
    }

    public function showAction(Event $event): ResponseInterface
    {
        $this->view->assignMultiple([
            'event' => $event,
            'isFull' => $event->isFull(),
            'isPast' => $event->isPast(),
        ]);

        return $this->htmlResponse();
    }

    public function showNextAction(): ResponseInterface
    {
        $event = $this->eventRepository->findNextUpcoming();

        if ($event === null) {
            $this->view->assign('noEvent', true);

            return $this->htmlResponse();
        }

        return $this->redirect('show', null, null, ['event' => $event]);
    }

    public function registerAction(Event $event): ResponseInterface
    {
        if ($this->request->getMethod() === 'POST') {
            $data = $this->request->getParsedBody();

            try {
                $this->registrationService->register($event, $data);
                $this->addFlashMessage(
                    sprintf('Vielen Dank, %s! Deine Anmeldung war erfolgreich.', htmlspecialchars((string) ($data['firstName'] ?? ''))),
                    'Erfolgreich registriert',
                    ContextualFeedbackSeverity::OK,
                );
            } catch (\RuntimeException $e) {
                $this->addFlashMessage(
                    $e->getMessage(),
                    'Fehler',
                    ContextualFeedbackSeverity::ERROR,
                );
            }
        }

        return $this->redirect('show', null, null, ['event' => $event]);
    }
}
