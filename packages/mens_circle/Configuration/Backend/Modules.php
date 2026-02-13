<?php

declare(strict_types=1);

use BeardCoder\MensCircle\Controller\EventBackendController;
use BeardCoder\MensCircle\Controller\NewsletterBackendController;

return [
    'web_menscircle_events' => [
        'parent' => 'content',
        'position' => ['after' => 'records'],
        'access' => 'user',
        'path' => '/module/content/menscircle/events',
        'iconIdentifier' => 'module-menscircle-events',
        'labels' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_mod_events.xlf',
        'extensionName' => 'MensCircle',
        'controllerActions' => [
            EventBackendController::class => [
                'index',
            ],
        ],
    ],
    'web_menscircle_newsletter' => [
        'parent' => 'content',
        'position' => ['after' => 'web_menscircle_events'],
        'access' => 'user',
        'path' => '/module/content/menscircle/newsletter',
        'iconIdentifier' => 'module-menscircle-newsletter',
        'labels' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_mod_newsletter.xlf',
        'extensionName' => 'MensCircle',
        'controllerActions' => [
            NewsletterBackendController::class => [
                'index',
                'send',
            ],
        ],
    ],
];
