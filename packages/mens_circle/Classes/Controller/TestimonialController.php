<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Controller;

use BeardCoder\MensCircle\Domain\Model\Testimonial;
use BeardCoder\MensCircle\Domain\Repository\TestimonialRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

final class TestimonialController extends ActionController
{
    public function __construct(
        private readonly TestimonialRepository $testimonialRepository,
    ) {}

    public function listAction(): ResponseInterface
    {
        $testimonials = $this->testimonialRepository->findApproved();
        $this->view->assign('testimonials', $testimonials);

        return $this->htmlResponse();
    }

    public function formAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

    public function submitAction(): ResponseInterface
    {
        if ($this->request->getMethod() !== 'POST') {
            return $this->redirect('form');
        }

        $data = $this->request->getParsedBody();
        $authorName = htmlspecialchars((string) ($data['authorName'] ?? ''));
        $content = htmlspecialchars((string) ($data['content'] ?? ''));

        if (empty($authorName) || empty($content)) {
            $this->addFlashMessage(
                'Bitte fülle alle Felder aus.',
                'Unvollständige Eingabe',
                ContextualFeedbackSeverity::ERROR,
            );

            return $this->redirect('form');
        }

        if (\strlen($content) < 20) {
            $this->addFlashMessage(
                'Das Testimonial muss mindestens 20 Zeichen lang sein.',
                'Zu kurz',
                ContextualFeedbackSeverity::ERROR,
            );

            return $this->redirect('form');
        }

        $testimonial = new Testimonial();
        $testimonial->setAuthorName($authorName);
        $testimonial->setContent($content);

        $this->testimonialRepository->add($testimonial);
        $this->objectManager->get(\TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface::class)->persistAll();

        $this->addFlashMessage(
            'Danke für dein Testimonial! Es wird nach einer Überprüfung veröffentlicht.',
            'Testimonial eingereicht',
            ContextualFeedbackSeverity::OK,
        );

        return $this->redirect('list');
    }
}
