<?php

declare(strict_types=1);

use MarkusSommer\MensCircle\Controller\EventController;
use MarkusSommer\MensCircle\Controller\NewsletterController;
use MarkusSommer\MensCircle\Controller\TestimonialController;
use MarkusSommer\MensCircle\Message\SendEventMailMessage;
use MarkusSommer\MensCircle\Message\SendEventSmsMessage;
use MarkusSommer\MensCircle\Message\SendNewsletterMessage;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

ExtensionUtility::configurePlugin(
    'MensCircle',
    'Event',
    [
        EventController::class => 'list',
    ],
    [],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

ExtensionUtility::configurePlugin(
    'MensCircle',
    'EventDetail',
    [
        EventController::class => 'detail,registerSuccess,ical',
    ],
    [
        EventController::class => 'register',
    ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

ExtensionUtility::configurePlugin(
    'MensCircle',
    'Newsletter',
    [
        NewsletterController::class => 'form,unsubscribe',
    ],
    [
        NewsletterController::class => 'subscribe,unsubscribe',
    ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

ExtensionUtility::configurePlugin(
    'MensCircle',
    'Testimonial',
    [
        TestimonialController::class => 'list',
    ],
    [],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

ExtensionUtility::configurePlugin(
    'MensCircle',
    'TestimonialForm',
    [
        TestimonialController::class => 'form,thanks',
    ],
    [
        TestimonialController::class => 'submit',
    ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

$GLOBALS['TYPO3_CONF_VARS']['SYS']['messenger']['routing'] ??= [];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['messenger']['routing'][SendNewsletterMessage::class] = 'doctrine';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['messenger']['routing'][SendEventMailMessage::class] = 'doctrine';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['messenger']['routing'][SendEventSmsMessage::class] = 'doctrine';

$GLOBALS['TYPO3_CONF_VARS']['BE']['stylesheets']['menscircle-backend-theme']
    = 'EXT:mens_circle/Resources/Public/Css/backend-theme.css';
