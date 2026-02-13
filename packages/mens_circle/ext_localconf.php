<?php

declare(strict_types=1);

use BeardCoder\MensCircle\Controller\EventController;
use BeardCoder\MensCircle\Controller\NewsletterController;
use BeardCoder\MensCircle\Controller\TestimonialController;
use BeardCoder\MensCircle\Message\SendEventNotificationMessage;
use BeardCoder\MensCircle\Message\SendNewsletterMessage;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') || die();

ExtensionUtility::configurePlugin(
    'MensCircle',
    'Event',
    [
        EventController::class => ['list'],
    ],
    [],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
);

ExtensionUtility::configurePlugin(
    'MensCircle',
    'EventDetail',
    [
        EventController::class => ['detail', 'register', 'registerSuccess', 'ical'],
    ],
    [
        EventController::class => ['register'],
    ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
);

ExtensionUtility::configurePlugin(
    'MensCircle',
    'Newsletter',
    [
        NewsletterController::class => ['form', 'subscribe', 'unsubscribe'],
    ],
    [
        NewsletterController::class => ['subscribe', 'unsubscribe'],
    ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
);

ExtensionUtility::configurePlugin(
    'MensCircle',
    'TestimonialForm',
    [
        TestimonialController::class => ['form', 'submit', 'thanks'],
    ],
    [
        TestimonialController::class => ['submit'],
    ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
);

$GLOBALS['TYPO3_CONF_VARS']['SYS']['messenger']['routing'] ??= [];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['messenger']['routing'][SendNewsletterMessage::class] = 'doctrine';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['messenger']['routing'][SendEventNotificationMessage::class] = 'doctrine';

$GLOBALS['TYPO3_CONF_VARS']['BE']['stylesheets']['menscircle-backend-theme']
    = 'EXT:mens_circle/Resources/Public/Css/backend-theme.css';
