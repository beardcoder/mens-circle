<?php

declare(strict_types=1);

namespace MarkusSommer\MensCircle\Controller;

use MarkusSommer\MensCircle\Domain\Model\Testimonial;
use MarkusSommer\MensCircle\Domain\Repository\TestimonialRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

final class TestimonialController extends ActionController
{
    public function __construct(
        private readonly TestimonialRepository $testimonialRepository,
        private readonly PersistenceManager $persistenceManager
    ) {
    }

    public function listAction(): ResponseInterface
    {
        $this->view->assign('testimonials', $this->testimonialRepository->findPublished());

        return $this->htmlResponse();
    }

    public function formAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

    public function submitAction(): ResponseInterface
    {
        $arguments = $this->request->getArguments();
        $quote = trim((string) ($arguments['quote'] ?? ''));
        $authorName = trim((string) ($arguments['authorName'] ?? ''));
        $role = trim((string) ($arguments['role'] ?? ''));
        $email = strtolower(trim((string) ($arguments['email'] ?? '')));
        $privacyAccepted = (bool) ((int) ($arguments['privacy'] ?? 0));

        if ($quote === '' || mb_strlen($quote) < 10 || $email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL) || ! $privacyAccepted) {
            $this->addFlashMessage('Bitte fülle alle Pflichtfelder korrekt aus.', '', ContextualFeedbackSeverity::ERROR);

            return $this->redirect('form');
        }

        $testimonial = new Testimonial();
        $testimonial->setQuote($quote);
        $testimonial->setAuthorName($authorName);
        $testimonial->setRole($role);
        $testimonial->setEmail($email);
        $testimonial->setIsPublished(false);
        $testimonial->setSortOrder(0);

        $this->testimonialRepository->add($testimonial);
        $this->persistenceManager->persistAll();

        return $this->redirect('thanks');
    }

    public function thanksAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }
}
