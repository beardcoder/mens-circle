<?php

declare(strict_types=1);

use BeardCoder\MensCircle\Controller\EventController;
use BeardCoder\MensCircle\Controller\NewsletterController;
use BeardCoder\MensCircle\Controller\TestimonialController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

ExtensionUtility::configurePlugin(
    'mens_circle',
    'Events',
    [
        EventController::class => ['list', 'show', 'showNext', 'register'],
    ],
    [
        EventController::class => ['register'],
    ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
);

ExtensionUtility::configurePlugin(
    'mens_circle',
    'Newsletter',
    [
        NewsletterController::class => ['subscribe', 'confirm', 'unsubscribe'],
    ],
    [
        NewsletterController::class => ['subscribe', 'confirm', 'unsubscribe'],
    ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
);

ExtensionUtility::configurePlugin(
    'mens_circle',
    'Testimonials',
    [
        TestimonialController::class => ['list', 'form', 'submit'],
    ],
    [
        TestimonialController::class => ['submit'],
    ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
);
