<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Controller;

use BeardCoder\MensCircle\Domain\Model\Testimonial;
use BeardCoder\MensCircle\Domain\Repository\TestimonialRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

final class TestimonialController extends ActionController
{
    private const MIN_QUOTE_LENGTH = 10;

    public function __construct(
        private readonly TestimonialRepository $testimonialRepository,
        private readonly PersistenceManager $persistenceManager,
    ) {}

    public function listAction(): ResponseInterface
    {
        $this->view->assign('testimonials', $this->testimonialRepository->findPublished());

        return $this->htmlResponse();
    }

    public function formAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

    public function submitAction(
        string $quote = '',
        string $authorName = '',
        string $role = '',
        string $email = '',
        bool $privacy = false,
    ): ResponseInterface {
        $normalizedQuote = trim($quote);
        $normalizedAuthorName = trim($authorName);
        $normalizedRole = trim($role);
        $normalizedEmail = strtolower(trim($email));

        if (!$this->isSubmissionValid($normalizedQuote, $normalizedEmail, $privacy)) {
            return $this->redirectToFormWithError();
        }

        $testimonial = $this->createPendingTestimonial(
            quote: $normalizedQuote,
            authorName: $normalizedAuthorName,
            role: $normalizedRole,
            email: $normalizedEmail,
        );
        $this->testimonialRepository->add($testimonial);
        $this->persistenceManager->persistAll();

        if ($this->isEnhancedRequest()) {
            return $this->htmlFormResult('Danke für deine Erfahrung! Dein Beitrag wird nach Prüfung veröffentlicht.', 'success');
        }

        return $this->redirect('thanks');
    }

    public function thanksAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

    private function isSubmissionValid(string $quote, string $email, bool $privacy): bool
    {
        if ($quote === '' || mb_strlen($quote) < self::MIN_QUOTE_LENGTH) {
            return false;
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        return $privacy;
    }

    private function isEnhancedRequest(): bool
    {
        return $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';
    }

    private function htmlFormResult(string $message, string $severity): ResponseInterface
    {
        $html = \sprintf('<div data-form-result="%s">%s</div>', $severity, htmlspecialchars($message));

        return $this->responseFactory->createResponse()
            ->withHeader('Content-Type', 'text/html; charset=utf-8')
            ->withBody($this->streamFactory->createStream($html));
    }

    private function redirectToFormWithError(): ResponseInterface
    {
        $errorMessage = 'Bitte fülle alle Pflichtfelder korrekt aus.';

        if ($this->isEnhancedRequest()) {
            return $this->htmlFormResult($errorMessage, 'error');
        }

        $this->addFlashMessage($errorMessage, '', ContextualFeedbackSeverity::ERROR);

        return $this->redirect('form');
    }

    private function createPendingTestimonial(
        string $quote,
        string $authorName,
        string $role,
        string $email,
    ): Testimonial {
        $testimonial = new Testimonial();
        $testimonial->quote = $quote;
        $testimonial->authorName = $authorName;
        $testimonial->role = $role;
        $testimonial->email = $email;
        $testimonial->isPublished = false;
        $testimonial->sortOrder = 0;

        return $testimonial;
    }
}
