<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Controller;

use BeardCoder\FluidForms\Trait\JsonFormResponder;
use BeardCoder\MensCircle\Domain\Model\Event;
use BeardCoder\MensCircle\Domain\Repository\EventRepository;
use BeardCoder\MensCircle\Service\RegistrationService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

final class EventController extends ActionController
{
    use JsonFormResponder;

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
        if ($this->request->getMethod() !== 'POST') {
            return $this->redirect('show', null, null, ['event' => $event]);
        }

        $data = $this->getFormData();

        $validator = $this->validateForm($data, [
            'firstName' => ['required', 'minLength:2'],
            'lastName' => ['required', 'minLength:2'],
            'email' => ['required', 'email'],
            'phone' => ['phone'],
            'notes' => ['maxLength:500'],
        ], [], [
            'firstName' => 'Vorname',
            'lastName' => 'Nachname',
            'email' => 'E-Mail',
            'phone' => 'Telefon',
            'notes' => 'Anmerkungen',
        ]);

        if ($validator->fails()) {
            if ($this->isJsonRequest()) {
                return $this->validationErrorResponse($validator);
            }

            return $this->redirect('show', null, null, ['event' => $event]);
        }

        try {
            $this->registrationService->register($event, $validator->validated());

            if ($this->isJsonRequest()) {
                return $this->successResponse(
                    sprintf('Vielen Dank, %s! Deine Anmeldung war erfolgreich.', $validator->get('firstName')),
                );
            }
        } catch (\RuntimeException $e) {
            if ($this->isJsonRequest()) {
                return $this->errorResponse($e->getMessage());
            }
        }

        return $this->redirect('show', null, null, ['event' => $event]);
    }
}
