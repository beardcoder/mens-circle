<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Controller;

use BeardCoder\FluidForms\Trait\JsonFormResponder;
use BeardCoder\MensCircle\Domain\Model\NewsletterSubscription;
use BeardCoder\MensCircle\Domain\Repository\NewsletterSubscriptionRepository;
use BeardCoder\MensCircle\Service\EmailService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

final class NewsletterController extends ActionController
{
    use JsonFormResponder;

    public function __construct(
        private readonly NewsletterSubscriptionRepository $newsletterSubscriptionRepository,
        private readonly EmailService $emailService,
        private readonly PersistenceManagerInterface $persistenceManager,
    ) {
    }

    public function subscribeAction(): ResponseInterface
    {
        if ($this->request->getMethod() !== 'POST') {
            return $this->htmlResponse();
        }

        $data = $this->getFormData();

        $validator = $this->validateForm($data, [
            'email' => ['required', 'email'],
            'firstName' => ['maxLength:100'],
        ], [], [
            'email' => 'E-Mail',
            'firstName' => 'Vorname',
        ]);

        if ($validator->fails()) {
            if ($this->isJsonRequest()) {
                return $this->validationErrorResponse($validator);
            }

            return $this->htmlResponse();
        }

        $email = $validator->get('email');
        $firstName = $validator->get('firstName', '');

        $existing = $this->newsletterSubscriptionRepository->findByEmail($email);

        if ($existing !== null) {
            if ($existing->isConfirmed()) {
                $message = 'Diese E-Mail-Adresse ist bereits angemeldet.';

                if ($this->isJsonRequest()) {
                    return $this->errorResponse($message);
                }

                return $this->htmlResponse();
            }

            $this->emailService->sendNewsletterConfirmation($existing);
            $message = 'Bestätigungsmail wurde erneut gesendet.';

            if ($this->isJsonRequest()) {
                return $this->successResponse($message);
            }

            return $this->htmlResponse();
        }

        $subscription = new NewsletterSubscription();
        $subscription->setEmail($email);
        $subscription->setFirstName($firstName);

        $this->newsletterSubscriptionRepository->add($subscription);
        $this->persistenceManager->persistAll();

        $this->emailService->sendNewsletterConfirmation($subscription);

        $message = 'Danke! Bitte bestätige deine Anmeldung mit dem Link in der gesendeten E-Mail.';

        if ($this->isJsonRequest()) {
            return $this->successResponse($message);
        }

        return $this->htmlResponse();
    }

    public function confirmAction(string $token): ResponseInterface
    {
        $subscription = $this->newsletterSubscriptionRepository->findByConfirmationToken($token);

        if ($subscription === null) {
            $this->view->assign('success', false);
            $this->view->assign('message', 'Der Bestätigungslink ist ungültig oder abgelaufen.');

            return $this->htmlResponse();
        }

        if ($subscription->isConfirmed()) {
            $this->view->assign('success', true);
            $this->view->assign('message', 'Deine Anmeldung war bereits bestätigt.');

            return $this->htmlResponse();
        }

        $subscription->confirm();
        $this->newsletterSubscriptionRepository->update($subscription);
        $this->persistenceManager->persistAll();

        $this->view->assign('success', true);
        $this->view->assign('message', 'Deine Newsletter-Anmeldung wurde erfolgreich bestätigt!');

        return $this->htmlResponse();
    }

    public function unsubscribeAction(?string $token = null): ResponseInterface
    {
        if ($this->request->getMethod() === 'POST') {
            $token = (string) ($this->request->getParsedBody()['token'] ?? '');
        }

        if ($token === null || $token === '') {
            $this->view->assign('showForm', true);

            return $this->htmlResponse();
        }

        $subscription = $this->newsletterSubscriptionRepository->findByUnsubscribeToken($token);

        if ($subscription === null) {
            $this->view->assign('showForm', false);
            $this->view->assign('success', false);
            $this->view->assign('message', 'Der Abmeldungslink ist ungültig.');

            return $this->htmlResponse();
        }

        $this->newsletterSubscriptionRepository->remove($subscription);
        $this->persistenceManager->persistAll();

        $this->view->assign('showForm', false);
        $this->view->assign('success', true);
        $this->view->assign('message', 'Du wurdest erfolgreich vom Newsletter abgemeldet.');

        return $this->htmlResponse();
    }
}
