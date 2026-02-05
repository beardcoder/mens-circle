<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Controller\Backend;

use BeardCoder\MensCircle\Domain\Repository\NewsletterSubscriptionRepository;
use BeardCoder\MensCircle\Service\EmailService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

final class NewsletterController extends ActionController
{
    public function __construct(
        private readonly NewsletterSubscriptionRepository $newsletterSubscriptionRepository,
        private readonly EmailService $emailService,
        private readonly ModuleTemplateFactory $moduleTemplateFactory,
        private readonly IconFactory $iconFactory,
        private readonly PersistenceManagerInterface $persistenceManager,
    ) {}

    public function listAction(): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        // Set title
        $moduleTemplate->setTitle('Newsletter Verwaltung');

        // Add DocHeader buttons
        $this->addListActionButtons($moduleTemplate);

        $allSubscriptions = $this->newsletterSubscriptionRepository->findAll();
        $confirmedSubscriptions = $this->newsletterSubscriptionRepository->findAllConfirmed();
        $pendingCount = $this->newsletterSubscriptionRepository->countByIsConfirmed(false);

        $moduleTemplate->assignMultiple([
            'subscriptions' => $allSubscriptions,
            'confirmedCount' => $confirmedSubscriptions->count(),
            'pendingCount' => $pendingCount,
            'totalCount' => $allSubscriptions->count(),
        ]);

        return $moduleTemplate->renderResponse('Backend/Newsletter/List');
    }

    public function composeAction(): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        // Set title
        $moduleTemplate->setTitle('Newsletter erstellen');

        // Add DocHeader buttons
        $this->addComposeActionButtons($moduleTemplate);

        $confirmedCount = $this->newsletterSubscriptionRepository->countConfirmed();

        $moduleTemplate->assignMultiple([
            'confirmedCount' => $confirmedCount,
        ]);

        return $moduleTemplate->renderResponse('Backend/Newsletter/Compose');
    }

    public function sendAction(string $subject = '', string $message = ''): ResponseInterface
    {
        if (empty($subject) || empty($message)) {
            $this->addFlashMessage(
                'Betreff und Nachricht dürfen nicht leer sein.',
                'Fehler',
                ContextualFeedbackSeverity::ERROR,
            );
            return $this->redirect('compose');
        }

        $subscribers = $this->newsletterSubscriptionRepository->findAllConfirmed();
        $sentCount = 0;
        $failedCount = 0;

        foreach ($subscribers as $subscriber) {
            try {
                $this->emailService->sendNewsletter($subscriber, $subject, $message);
                $sentCount++;
            } catch (\Exception $e) {
                $failedCount++;
            }
        }

        if ($failedCount > 0) {
            $this->addFlashMessage(
                \sprintf(
                    'Newsletter wurde an %d Empfänger gesendet. %d Fehler sind aufgetreten.',
                    $sentCount,
                    $failedCount,
                ),
                'Teilweise erfolgreich',
                ContextualFeedbackSeverity::WARNING,
            );
        } else {
            $this->addFlashMessage(
                \sprintf('Newsletter wurde erfolgreich an %d Empfänger gesendet.', $sentCount),
                'Erfolgreich',
                ContextualFeedbackSeverity::OK,
            );
        }

        return $this->redirect('list');
    }

    public function deleteAction(int $subscriptionUid): ResponseInterface
    {
        $subscription = $this->newsletterSubscriptionRepository->findByUid($subscriptionUid);

        if ($subscription === null) {
            $this->addFlashMessage(
                'Abonnement nicht gefunden.',
                'Fehler',
                ContextualFeedbackSeverity::ERROR,
            );
            return $this->redirect('list');
        }

        $this->newsletterSubscriptionRepository->remove($subscription);
        $this->persistenceManager->persistAll();

        $this->addFlashMessage(
            'Abonnement wurde erfolgreich gelöscht.',
            'Erfolgreich',
            ContextualFeedbackSeverity::OK,
        );

        return $this->redirect('list');
    }

    private function addListActionButtons(ModuleTemplate $moduleTemplate): void
    {
        $buttonBar = $moduleTemplate->getDocHeaderComponent()->getButtonBar();

        // Add "Create Newsletter" button as primary action
        $newButton = $buttonBar->makeLinkButton()
            ->setHref($this->uriBuilder->reset()->uriFor('compose'))
            ->setTitle('Newsletter erstellen')
            ->setShowLabelText(true)
            ->setClasses('btn-primary')
            ->setIcon($this->iconFactory->getIcon('actions-document-new', \TYPO3\CMS\Core\Imaging\IconSize::SMALL));

        $buttonBar->addButton($newButton, ButtonBar::BUTTON_POSITION_LEFT, 1);

        // Add reload button
        $reloadButton = $buttonBar->makeLinkButton()
            ->setHref($this->uriBuilder->reset()->uriFor('list'))
            ->setTitle('Neu laden')
            ->setIcon($this->iconFactory->getIcon('actions-refresh', \TYPO3\CMS\Core\Imaging\IconSize::SMALL));

        $buttonBar->addButton($reloadButton, ButtonBar::BUTTON_POSITION_RIGHT);
    }

    private function addComposeActionButtons(ModuleTemplate $moduleTemplate): void
    {
        $buttonBar = $moduleTemplate->getDocHeaderComponent()->getButtonBar();

        // Add "Back" button
        $backButton = $buttonBar->makeLinkButton()
            ->setHref($this->uriBuilder->reset()->uriFor('list'))
            ->setTitle('Zurück zur Übersicht')
            ->setShowLabelText(true)
            ->setIcon($this->iconFactory->getIcon('actions-arrow-left', \TYPO3\CMS\Core\Imaging\IconSize::SMALL));

        $buttonBar->addButton($backButton, ButtonBar::BUTTON_POSITION_LEFT);
    }
}
