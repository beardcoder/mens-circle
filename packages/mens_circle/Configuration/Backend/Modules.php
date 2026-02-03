<?php

declare(strict_types=1);

use BeardCoder\MensCircle\Controller\Backend\NewsletterController;

return [
    'web_MensCircleEvents' => [
        'path' => '/mens-circle/events',
        'labels' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang.xlf:module.events',
        'iconidentifier' => 'tx-menscircle-event',
        'navigationComponents' => [
            'app:module-tree',
        ],
    ],
    'web_MensCircleNewsletter' => [
        'parent' => 'web',
        'position' => ['after' => 'web_list'],
        'access' => 'user',
        'workspaces' => 'live',
        'path' => '/module/web/MensCircleNewsletter',
        'labels' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_mod.xlf',
        'extensionName' => 'MensCircle',
        'controllerActions' => [
            NewsletterController::class => [
                'list',
                'compose',
                'send',
                'delete',
            ],
        ],
    ],
];
