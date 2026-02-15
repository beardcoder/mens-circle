<?php

declare(strict_types=1);

return [
    'frontend' => [
        'menscircle/sentry' => [
            'target' => \BeardCoder\MensCircle\Middleware\SentryMiddleware::class,
            'before' => [
                'typo3/cms-frontend/page-resolver',
            ],
            'after' => [
                'typo3/cms-core/normalized-params-attribute',
            ],
        ],
    ],
];
