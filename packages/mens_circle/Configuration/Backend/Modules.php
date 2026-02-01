<?php

declare(strict_types=1);

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
        'path' => '/mens-circle/newsletter',
        'labels' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang.xlf:module.newsletter',
        'iconidentifier' => 'tx-menscircle-newsletter',
        'navigationComponents' => [
            'app:module-tree',
        ],
    ],
];
