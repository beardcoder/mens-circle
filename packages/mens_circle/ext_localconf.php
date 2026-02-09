<?php

declare(strict_types=1);

use MarkusSommer\MensCircle\Controller\EventController;
use MarkusSommer\MensCircle\Controller\NewsletterController;
use MarkusSommer\MensCircle\Controller\TestimonialController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

ExtensionUtility::configurePlugin(
    'MensCircle',
    'Event',
    [
        EventController::class => 'list,show,registerSuccess,ical',
    ],
    [
        EventController::class => 'register',
    ]
);

ExtensionUtility::configurePlugin(
    'MensCircle',
    'Newsletter',
    [
        NewsletterController::class => 'form,unsubscribe',
    ],
    [
        NewsletterController::class => 'subscribe,unsubscribe',
    ]
);

ExtensionUtility::configurePlugin(
    'MensCircle',
    'Testimonial',
    [
        TestimonialController::class => 'list,form,thanks',
    ],
    [
        TestimonialController::class => 'submit',
    ]
);
