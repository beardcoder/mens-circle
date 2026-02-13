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

        if (! $this->isSubmissionValid($normalizedQuote, $normalizedEmail, $privacy)) {
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

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        return $privacy;
    }

    private function redirectToFormWithError(): ResponseInterface
    {
        $this->addFlashMessage('Bitte fülle alle Pflichtfelder korrekt aus.', '', ContextualFeedbackSeverity::ERROR);

        return $this->redirect('form');
    }

    private function createPendingTestimonial(
        string $quote,
        string $authorName,
        string $role,
        string $email,
    ): Testimonial {
        $testimonial = new Testimonial();
        $testimonial->setQuote($quote);
        $testimonial->setAuthorName($authorName);
        $testimonial->setRole($role);
        $testimonial->setEmail($email);
        $testimonial->setIsPublished(false);
        $testimonial->setSortOrder(0);

        return $testimonial;
    }
}
