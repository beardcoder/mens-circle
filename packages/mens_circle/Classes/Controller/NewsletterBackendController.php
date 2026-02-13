<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Controller;

use BeardCoder\MensCircle\Message\SendNewsletterMessage;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\SystemResource\Publishing\SystemResourcePublisherInterface;
use TYPO3\CMS\Core\SystemResource\SystemResourceFactory;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;

final class NewsletterBackendController extends ActionController
{
    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly ModuleTemplateFactory $moduleTemplateFactory,
        private readonly MessageBusInterface $messageBus,
        private readonly SystemResourceFactory $systemResourceFactory,
        private readonly SystemResourcePublisherInterface $systemResourcePublisher
    ) {}

    public function indexAction(): ResponseInterface
    {
        $recipients = $this->fetchRecipients();
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        return $moduleTemplate
            ->setFlashMessageQueue($this->getFlashMessageQueue())
            ->setTitle($this->translate('headline'))
            ->assignMultiple([
                'recipientCount' => \count($recipients),
                'recipients' => $this->buildRecipientPreview($recipients),
                'rteOptionsJson' => $this->buildRteOptionsJson(),
            ])
            ->renderResponse('NewsletterBackend/Index');
    }

    public function sendAction(): ResponseInterface
    {
        $subject = trim($this->resolveRequestArgument('subject'));
        $content = $this->resolveRequestArgument('content');

        if ($subject === '' || $this->isNewsletterContentEmpty($content)) {
            $this->addFlashMessage(
                'Betreff und Inhalt sind Pflichtfelder.',
                '',
                ContextualFeedbackSeverity::ERROR
            );

            return $this->redirect('index');
        }

        $recipients = $this->fetchRecipients();
        if ($recipients === []) {
            $this->addFlashMessage(
                'Es sind keine aktiven Newsletter-Abonnenten vorhanden.',
                '',
                ContextualFeedbackSeverity::INFO
            );

            return $this->redirect('index');
        }

        $dispatchResult = $this->dispatchMessages(
            recipients: $recipients,
            subject: $subject,
            content: $content
        );
        $dispatchedCount = $dispatchResult['dispatched'];
        $failedCount = $dispatchResult['failed'];

        if ($dispatchedCount > 0) {
            $this->addFlashMessage(
                \sprintf('Newsletter an den Message Bus übergeben: %d Empfänger.', $dispatchedCount),
                '',
                ContextualFeedbackSeverity::OK
            );
        }

        if ($failedCount > 0) {
            $this->addFlashMessage(
                \sprintf('Übergabe an den Message Bus nicht vollständig: %d Empfänger konnten nicht übergeben werden.', $failedCount),
                '',
                ContextualFeedbackSeverity::WARNING
            );
        }

        return $this->redirect('index');
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchRecipients(): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_menscircle_domain_model_newslettersubscription');
        $queryBuilder->getRestrictions()->removeByType(DeletedRestriction::class);

        $rows = $queryBuilder
            ->select(
                'subscription.token',
                'participant.email',
                'participant.first_name',
                'participant.last_name'
            )
            ->from('tx_menscircle_domain_model_newslettersubscription', 'subscription')
            ->innerJoin(
                'subscription',
                'tx_menscircle_domain_model_participant',
                'participant',
                $queryBuilder->expr()->eq('participant.uid', $queryBuilder->quoteIdentifier('subscription.participant'))
            )
            ->where(
                $queryBuilder->expr()->eq('subscription.deleted', $queryBuilder->createNamedParameter(0)),
                $queryBuilder->expr()->eq('subscription.hidden', $queryBuilder->createNamedParameter(0)),
                $queryBuilder->expr()->isNull('subscription.unsubscribed_at'),
                $queryBuilder->expr()->eq('participant.deleted', $queryBuilder->createNamedParameter(0)),
                $queryBuilder->expr()->eq('participant.hidden', $queryBuilder->createNamedParameter(0)),
                $queryBuilder->expr()->neq('participant.email', $queryBuilder->createNamedParameter(''))
            )
            ->orderBy('participant.email', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();

        return \is_array($rows) ? $rows : [];
    }

    /**
     * @param list<array<string, mixed>> $recipients
     * @return list<array{name: string, email: string}>
     */
    private function buildRecipientPreview(array $recipients): array
    {
        return array_map(
            fn(array $recipient): array => [
                'name' => $this->buildRecipientName($recipient),
                'email' => (string) ($recipient['email'] ?? ''),
            ],
            $recipients
        );
    }

    /**
     * @param array<string, mixed> $recipient
     */
    private function buildRecipientName(array $recipient): string
    {
        $firstName = trim((string) ($recipient['first_name'] ?? ''));
        $lastName = trim((string) ($recipient['last_name'] ?? ''));

        $fullName = trim($firstName . ' ' . $lastName);

        return $fullName !== '' ? $fullName : '-';
    }

    private function buildUnsubscribeUrl(string $baseUrl, int $newsletterPid, string $token): string
    {
        if ($baseUrl === '' || $newsletterPid <= 0 || $token === '') {
            return '';
        }

        $query = http_build_query([
            'id' => $newsletterPid,
            'tx_menscircle_newsletter' => [
                'action' => 'unsubscribe',
                'controller' => 'Newsletter',
                'token' => $token,
            ],
        ]);

        return $baseUrl . '/?' . $query;
    }

    private function isNewsletterContentEmpty(string $content): bool
    {
        $normalizedContent = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $normalizedContent = str_replace("\xc2\xa0", ' ', $normalizedContent);

        return trim(strip_tags($normalizedContent)) === '';
    }

    /**
     * @param list<array<string, mixed>> $recipients
     * @return array{dispatched: int, failed: int}
     */
    private function dispatchMessages(array $recipients, string $subject, string $content): array
    {
        $baseUrl = rtrim((string) ($this->settings['baseUrl'] ?? ''), '/');
        $newsletterPid = (int) ($this->settings['newsletterPid'] ?? 0);
        $settings = \is_array($this->settings) ? $this->settings : [];

        $dispatchedCount = 0;
        $failedCount = 0;
        foreach ($recipients as $recipient) {
            try {
                $this->messageBus->dispatch(new SendNewsletterMessage(
                    toEmail: (string) ($recipient['email'] ?? ''),
                    toName: $this->buildRecipientName($recipient),
                    subject: $subject,
                    content: $content,
                    unsubscribeUrl: $this->buildUnsubscribeUrl(
                        $baseUrl,
                        $newsletterPid,
                        (string) ($recipient['token'] ?? '')
                    ),
                    settings: $settings
                ));
                $dispatchedCount++;
            } catch (\Throwable) {
                $failedCount++;
            }
        }

        return [
            'dispatched' => $dispatchedCount,
            'failed' => $failedCount,
        ];
    }

    private function resolveRequestArgument(string $name): string
    {
        try {
            if (! $this->request->hasArgument($name)) {
                return '';
            }

            return (string) $this->request->getArgument($name);
        } catch (NoSuchArgumentException) {
            return '';
        }
    }

    private function buildRteOptionsJson(): string
    {
        $rteOptions = [
            'toolbar' => [
                'items' => [
                    'style',
                    'heading',
                    '|',
                    'bold',
                    'italic',
                    'subscript',
                    'superscript',
                    '|',
                    'bulletedList',
                    'numberedList',
                    'blockQuote',
                    '|',
                    'link',
                    'removeFormat',
                    '|',
                    'undo',
                    'redo',
                    '|',
                    'sourceEditing',
                ],
            ],
            'contentsCss' => [
                $this->resolvePublicResourceUri('EXT:rte_ckeditor/Resources/Public/Css/contents.css'),
            ],
            'height' => 320,
            'width' => 'auto',
            'language' => [
                'ui' => $this->resolveBackendLanguage(),
                'content' => 'de',
            ],
        ];

        return htmlspecialchars(
            (string) json_encode($rteOptions, JSON_THROW_ON_ERROR),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    }

    private function resolvePublicResourceUri(string $resourceIdentifier): string
    {
        $resource = $this->systemResourceFactory->createPublicResource($resourceIdentifier);

        return (string) $this->systemResourcePublisher->generateUri($resource, null);
    }

    private function resolveBackendLanguage(): string
    {
        $backendUserLanguage = (string) ($GLOBALS['BE_USER']->uc['lang'] ?? 'en');
        if ($backendUserLanguage === '' || $backendUserLanguage === 'default') {
            return 'en';
        }

        $sanitizedLanguage = preg_replace('/[^a-zA-Z_-]/', '', $backendUserLanguage);

        return $sanitizedLanguage !== null && $sanitizedLanguage !== '' ? $sanitizedLanguage : 'en';
    }

    private function translate(string $key): string
    {
        $languageKey = 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_mod_newsletter.xlf:' . $key;
        $translated = $GLOBALS['LANG']?->sL($languageKey);

        return \is_string($translated) && $translated !== '' ? $translated : $key;
    }
}
