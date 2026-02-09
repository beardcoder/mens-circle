<?php

declare(strict_types=1);

return [
    'ctrl' => [
        'title' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.participant.plural',
        'label' => 'email',
        'label_alt' => 'first_name,last_name',
        'label_alt_force' => true,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'first_name,last_name,email,phone',
        'iconfile' => 'EXT:mens_circle/Resources/Public/Icons/Record/participant.svg',
    ],
    'types' => [
        '1' => [
            'showitem' => 'hidden, first_name, last_name, email, phone',
        ],
    ],
    'columns' => [
        'hidden' => [
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
                'default' => 0,
            ],
        ],
        'first_name' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.participant.first_name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'last_name' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.participant.last_name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'email' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.participant.email',
            'config' => [
                'type' => 'input',
                'size' => 40,
                'required' => true,
                'eval' => 'trim,lower,email',
            ],
        ],
        'phone' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.participant.phone',
            'config' => [
                'type' => 'input',
                'size' => 20,
                'eval' => 'trim',
            ],
        ],
    ],
];
