<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Controller;

use BeardCoder\FluidForms\Trait\JsonFormResponder;
use BeardCoder\MensCircle\Domain\Model\Testimonial;
use BeardCoder\MensCircle\Domain\Repository\TestimonialRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

final class TestimonialController extends ActionController
{
    use JsonFormResponder;

    public function __construct(
        private readonly TestimonialRepository $testimonialRepository,
        private readonly PersistenceManagerInterface $persistenceManager,
    ) {
    }

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

        $data = $this->getFormData();

        $validator = $this->validateForm($data, [
            'authorName' => ['required', 'minLength:2'],
            'content' => ['required', 'minLength:20'],
        ], [
            'content.minLength' => 'Das Testimonial muss mindestens 20 Zeichen lang sein.',
        ], [
            'authorName' => 'Name',
            'content' => 'Testimonial',
        ]);

        if ($validator->fails()) {
            if ($this->isJsonRequest()) {
                return $this->validationErrorResponse($validator);
            }

            return $this->redirect('form');
        }

        $testimonial = new Testimonial();
        $testimonial->setAuthorName($validator->get('authorName'));
        $testimonial->setContent($validator->get('content'));

        $this->testimonialRepository->add($testimonial);
        $this->persistenceManager->persistAll();

        $message = 'Danke für dein Testimonial! Es wird nach einer Überprüfung veröffentlicht.';

        if ($this->isJsonRequest()) {
            return $this->successResponse($message);
        }

        return $this->redirect('list');
    }
}
