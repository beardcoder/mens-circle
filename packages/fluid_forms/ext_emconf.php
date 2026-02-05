<?php

declare(strict_types=1);

$EM_CONF[$_EXTKEY] = [
    'title' => 'Fluid Forms',
    'description' => 'Lightweight dynamic AJAX form handling for TYPO3 Extbase. Declarative validation, automatic JSON responses, and zero-config JavaScript form interception.',
    'category' => 'fe',
    'author' => 'beardcoder',
    'author_email' => '',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '14.1.0-14.99.99',
            'php' => '8.3.0-8.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'autoload' => [
        'psr-4' => [
            'BeardCoder\\FluidForms\\' => 'Classes',
        ],
    ],
];
