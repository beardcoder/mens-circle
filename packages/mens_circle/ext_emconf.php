<?php

declare(strict_types=1);

$EM_CONF[$_EXTKEY] = [
    'title' => 'Männerkreis Sitepackage',
    'description' => 'Core-nahes TYPO3-Sitepackage mit Events, Newsletter und Testimonials.',
    'category' => 'plugin',
    'author' => 'Markus Sommer',
    'author_email' => '',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '14.1.0-14.9.99',
        ],
    ],
];
