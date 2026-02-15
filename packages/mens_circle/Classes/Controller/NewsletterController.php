<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Controller;

use BeardCoder\MensCircle\Domain\Model\NewsletterSubscription;
use BeardCoder\MensCircle\Domain\Model\Participant;
use BeardCoder\MensCircle\Domain\Repository\NewsletterSubscriptionRepository;
use BeardCoder\MensCircle\Domain\Repository\ParticipantRepository;
use BeardCoder\MensCircle\Service\MailService;
use DateTime;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

final class NewsletterController extends ActionController
{
    public function __construct(
        private readonly ParticipantRepository $participantRepository,
        private readonly NewsletterSubscriptionRepository $newsletterSubscriptionRepository,
        private readonly PersistenceManager $persistenceManager,
        private readonly MailService $mailService,
    ) {}

    public function formAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

    public function subscribeAction(string $email = '', bool $privacy = false): ResponseInterface
    {
        $normalizedEmail = strtolower(trim($email));
        if (!$this->isSubscriptionInputValid($normalizedEmail, $privacy)) {
            return $this->redirectToFormWithMessage(
                'Bitte gib eine gültige E-Mail-Adresse ein und bestätige den Datenschutz.',
                ContextualFeedbackSeverity::ERROR,
            );
        }

        $participant = $this->findOrCreateParticipant($normalizedEmail);
        $subscription = $this->newsletterSubscriptionRepository->findOneByParticipant($participant);
        if ($subscription instanceof NewsletterSubscription && $subscription->isActive()) {
            return $this->redirectToFormWithMessage('Diese E-Mail-Adresse ist bereits angemeldet.', ContextualFeedbackSeverity::INFO);
        }

        $subscription = $this->activateSubscription($participant, $subscription);
        $this->persistenceManager->persistAll();

        $unsubscribeUrl = $this->buildUnsubscribeUrl($subscription->token);
        $this->mailService->sendNewsletterWelcome($subscription, $unsubscribeUrl, $this->settings);

        return $this->redirectToFormWithMessage('Danke! Du bist jetzt für den Newsletter angemeldet.', ContextualFeedbackSeverity::OK);
    }

    public function unsubscribeAction(string $token = ''): ResponseInterface
    {
        if ($token === '') {
            return $this->renderUnsubscribeMessage('Ungültiger Abmeldelink.');
        }

        $subscription = $this->newsletterSubscriptionRepository->findOneByToken($token);
        if (!$subscription instanceof NewsletterSubscription) {
            return $this->renderUnsubscribeMessage('Dieser Abmeldelink ist nicht mehr gültig.');
        }

        if (!$subscription->isActive()) {
            return $this->renderUnsubscribeMessage('Diese E-Mail-Adresse wurde bereits abgemeldet.');
        }

        $subscription->unsubscribedAt = new DateTime();
        $this->newsletterSubscriptionRepository->update($subscription);
        $this->persistenceManager->persistAll();

        return $this->renderUnsubscribeMessage('Du wurdest erfolgreich vom Newsletter abgemeldet.');
    }

    private function isSubscriptionInputValid(string $email, bool $privacy): bool
    {
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        return $privacy;
    }

    private function findOrCreateParticipant(string $email): Participant
    {
        $participant = $this->participantRepository->findOneByEmail($email);
        if ($participant instanceof Participant) {
            return $participant;
        }

        $participant = new Participant();
        $participant->email = $email;
        $this->participantRepository->add($participant);

        return $participant;
    }

    private function activateSubscription(
        Participant $participant,
        ?NewsletterSubscription $subscription,
    ): NewsletterSubscription {
        if (!$subscription instanceof NewsletterSubscription) {
            $subscription = new NewsletterSubscription();
            $subscription->participant = $participant;
            $subscription->token = bin2hex(random_bytes(32));
            $subscription->subscribedAt = new DateTime();
            $subscription->unsubscribedAt = null;
            $this->newsletterSubscriptionRepository->add($subscription);

            return $subscription;
        }

        $subscription->subscribedAt = new DateTime();
        $subscription->unsubscribedAt = null;
        if ($subscription->token === '') {
            $subscription->token = bin2hex(random_bytes(32));
        }
        $this->newsletterSubscriptionRepository->update($subscription);

        return $subscription;
    }

    private function buildUnsubscribeUrl(string $token): string
    {
        $newsletterPid = (int)($this->settings['newsletterPid'] ?? 0);
        $uriBuilder = $this->uriBuilder
            ->reset()
            ->setCreateAbsoluteUri(true);

        if ($newsletterPid > 0) {
            $uriBuilder->setTargetPageUid($newsletterPid);
        }

        return $uriBuilder->uriFor(
            'unsubscribe',
            ['token' => $token],
            'Newsletter',
            'MensCircle',
            'Newsletter',
        );
    }

    private function redirectToFormWithMessage(
        string $message,
        ContextualFeedbackSeverity $severity,
    ): ResponseInterface {
        $this->addFlashMessage($message, '', $severity);

        return $this->redirect('form');
    }

    private function renderUnsubscribeMessage(string $message): ResponseInterface
    {
        $this->view->assign('message', $message);

        return $this->htmlResponse();
    }
}
