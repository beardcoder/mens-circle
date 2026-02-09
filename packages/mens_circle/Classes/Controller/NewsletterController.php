<?php

declare(strict_types=1);

namespace MarkusSommer\MensCircle\Controller;

use MarkusSommer\MensCircle\Domain\Model\NewsletterSubscription;
use MarkusSommer\MensCircle\Domain\Model\Participant;
use MarkusSommer\MensCircle\Domain\Repository\NewsletterSubscriptionRepository;
use MarkusSommer\MensCircle\Domain\Repository\ParticipantRepository;
use MarkusSommer\MensCircle\Service\MailService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

final class NewsletterController extends ActionController
{
    public function __construct(
        private readonly ParticipantRepository $participantRepository,
        private readonly NewsletterSubscriptionRepository $newsletterSubscriptionRepository,
        private readonly PersistenceManager $persistenceManager,
        private readonly MailService $mailService
    ) {
    }

    public function formAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

    public function subscribeAction(): ResponseInterface
    {
        $arguments = $this->request->getArguments();
        $email = strtolower(trim((string) ($arguments['email'] ?? '')));
        $privacyAccepted = (bool) ((int) ($arguments['privacy'] ?? 0));

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL) || ! $privacyAccepted) {
            $this->addFlashMessage('Bitte gib eine gültige E-Mail-Adresse ein und bestätige den Datenschutz.', '', AbstractMessage::ERROR);

            return $this->redirect('form');
        }

        $participant = $this->participantRepository->findOneByEmail($email);
        if (! $participant instanceof Participant) {
            $participant = new Participant();
            $participant->setEmail($email);
            $this->participantRepository->add($participant);
        }

        $subscription = $this->newsletterSubscriptionRepository->findOneByParticipant($participant);
        if ($subscription instanceof NewsletterSubscription && $subscription->isActive()) {
            $this->addFlashMessage('Diese E-Mail-Adresse ist bereits angemeldet.', '', AbstractMessage::INFO);

            return $this->redirect('form');
        }

        if (! $subscription instanceof NewsletterSubscription) {
            $subscription = new NewsletterSubscription();
            $subscription->setParticipant($participant);
            $subscription->setToken(bin2hex(random_bytes(32)));
            $subscription->setSubscribedAt(new \DateTime());
            $subscription->setUnsubscribedAt(null);
            $this->newsletterSubscriptionRepository->add($subscription);
        } else {
            $subscription->setSubscribedAt(new \DateTime());
            $subscription->setUnsubscribedAt(null);
            if ($subscription->getToken() === '') {
                $subscription->setToken(bin2hex(random_bytes(32)));
            }
            $this->newsletterSubscriptionRepository->update($subscription);
        }
        $this->persistenceManager->persistAll();

        $newsletterPid = (int) ($this->settings['newsletterPid'] ?? 0);
        $uriBuilder = $this->uriBuilder
            ->reset()
            ->setCreateAbsoluteUri(true);

        if ($newsletterPid > 0) {
            $uriBuilder->setTargetPageUid($newsletterPid);
        }

        $unsubscribeUrl = $uriBuilder->uriFor(
            'unsubscribe',
            ['token' => $subscription->getToken()],
            'Newsletter',
            'MensCircle',
            'Newsletter'
        );

        $this->mailService->sendNewsletterWelcome($subscription, $unsubscribeUrl, $this->settings);
        $this->addFlashMessage('Danke! Du bist jetzt für den Newsletter angemeldet.', '', AbstractMessage::OK);

        return $this->redirect('form');
    }

    public function unsubscribeAction(string $token = ''): ResponseInterface
    {
        if ($token === '') {
            $this->view->assign('message', 'Ungültiger Abmeldelink.');

            return $this->htmlResponse();
        }

        $subscription = $this->newsletterSubscriptionRepository->findOneByToken($token);
        if (! $subscription instanceof NewsletterSubscription) {
            $this->view->assign('message', 'Dieser Abmeldelink ist nicht mehr gültig.');

            return $this->htmlResponse();
        }

        if (! $subscription->isActive()) {
            $this->view->assign('message', 'Diese E-Mail-Adresse wurde bereits abgemeldet.');

            return $this->htmlResponse();
        }

        $subscription->setUnsubscribedAt(new \DateTime());
        $this->newsletterSubscriptionRepository->update($subscription);
        $this->persistenceManager->persistAll();

        $this->view->assign('message', 'Du wurdest erfolgreich vom Newsletter abgemeldet.');

        return $this->htmlResponse();
    }
}
