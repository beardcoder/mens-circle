<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Controller;

use BeardCoder\MensCircle\Domain\Model\NewsletterSubscription;
use BeardCoder\MensCircle\Domain\Repository\NewsletterSubscriptionRepository;
use BeardCoder\MensCircle\Service\EmailService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

final class NewsletterController extends ActionController
{
    public function __construct(
        private readonly NewsletterSubscriptionRepository $newsletterSubscriptionRepository,
        private readonly EmailService $emailService,
    ) {}

    public function subscribeAction(): ResponseInterface
    {
        if ($this->request->getMethod() === 'POST') {
            $data = $this->request->getParsedBody();
            $email = filter_var((string) ($data['email'] ?? ''), FILTER_SANITIZE_EMAIL);
            $firstName = htmlspecialchars((string) ($data['firstName'] ?? ''));

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addFlashMessage(
                    'Bitte gib eine gültige E-Mail-Adresse ein.',
                    'Ungültige E-Mail',
                    ContextualFeedbackSeverity::ERROR,
                );

                return $this->htmlResponse();
            }

            $existing = $this->newsletterSubscriptionRepository->findByEmail($email);

            if ($existing !== null) {
                if ($existing->isConfirmed()) {
                    $this->addFlashMessage(
                        'Diese E-Mail-Adresse ist bereits angemeldet.',
                        'Bereits angemeldet',
                        ContextualFeedbackSeverity::WARNING,
                    );
                } else {
                    $this->emailService->sendNewsletterConfirmation($existing);
                    $this->addFlashMessage(
                        'Bestätigungsmail wurde erneut gesendet.',
                        'Mail gesendet',
                        ContextualFeedbackSeverity::INFO,
                    );
                }

                return $this->htmlResponse();
            }

            $subscription = new NewsletterSubscription();
            $subscription->setEmail($email);
            $subscription->setFirstName($firstName);

            $this->newsletterSubscriptionRepository->add($subscription);
            $this->objectManager->get(\TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface::class)->persistAll();

            $this->emailService->sendNewsletterConfirmation($subscription);

            $this->addFlashMessage(
                'Danke! Bitte bestätige deine Anmeldung mit dem Link in der gesendeten E-Mail.',
                'Bestätigung ausstehend',
                ContextualFeedbackSeverity::OK,
            );
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
        $this->objectManager->get(\TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface::class)->persistAll();

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
        $this->objectManager->get(\TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface::class)->persistAll();

        $this->view->assign('showForm', false);
        $this->view->assign('success', true);
        $this->view->assign('message', 'Du wurdest erfolgreich vom Newsletter abgemeldet.');

        return $this->htmlResponse();
    }
}
