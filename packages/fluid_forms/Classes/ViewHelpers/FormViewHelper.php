<?php

declare(strict_types=1);

namespace BeardCoder\FluidForms\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

/**
 * ViewHelper that renders a <form> tag with FluidForms data attributes.
 *
 * Usage:
 *   <ff:form action="{f:uri.action(action: 'register')}" class="my-form">
 *     <input name="email" data-rules="required|email" data-label="E-Mail" />
 *     <button type="submit">Send</button>
 *   </ff:form>
 *
 * Renders:
 *   <form action="/my-action" method="post" data-fluid-form class="my-form">
 *     ...
 *   </form>
 */
class FormViewHelper extends AbstractTagBasedViewHelper
{
    protected $tagName = 'form';

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerUniversalTagAttributes();
        $this->registerArgument('action', 'string', 'Form action URL', true);
        $this->registerArgument('method', 'string', 'HTTP method', false, 'post');
        $this->registerArgument('resetOnSuccess', 'bool', 'Reset form after success', false, true);
        $this->registerArgument('scrollToError', 'bool', 'Scroll to first error', false, true);
        $this->registerArgument('successMessage', 'string', 'Custom success message', false, null);
        $this->registerArgument('loadingText', 'string', 'Custom loading text for submit button', false, null);
    }

    public function render(): string
    {
        $this->tag->addAttribute('action', $this->arguments['action']);
        $this->tag->addAttribute('method', $this->arguments['method']);
        $this->tag->addAttribute('data-fluid-form', '');

        if (!$this->arguments['resetOnSuccess']) {
            $this->tag->addAttribute('data-reset-on-success', 'false');
        }

        if (!$this->arguments['scrollToError']) {
            $this->tag->addAttribute('data-scroll-to-error', 'false');
        }

        if ($this->arguments['successMessage'] !== null) {
            $this->tag->addAttribute('data-success-message', $this->arguments['successMessage']);
        }

        if ($this->arguments['loadingText'] !== null) {
            $this->tag->addAttribute('data-loading-text', $this->arguments['loadingText']);
        }

        $this->tag->setContent($this->renderChildren());

        return $this->tag->render();
    }
}
