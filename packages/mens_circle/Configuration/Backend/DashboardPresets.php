<?php

declare(strict_types=1);

return [
    'menscircle-events' => [
        'title' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:dashboard.preset.events.title',
        'description' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:dashboard.preset.events.description',
        'iconIdentifier' => 'module-menscircle-events',
        'defaultWidgets' => [
            'menscircleUpcomingEvents',
            'menscircleUpcomingRegistrations',
            'menscircleNextEvent',
        ],
        'showInWizard' => true,
    ],
];
