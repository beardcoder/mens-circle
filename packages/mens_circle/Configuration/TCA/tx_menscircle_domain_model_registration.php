<?php

declare(strict_types=1);

return [
    'ctrl' => [
        'title' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.registration.plural',
        'label' => 'participant',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'status',
        'iconfile' => 'EXT:mens_circle/Resources/Public/Icons/Record/registration.svg',
    ],
    'types' => [
        '1' => [
            'showitem' => 'hidden, event, participant, status, registered_at, cancelled_at',
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
        'event' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.registration.event',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_menscircle_domain_model_event',
                'minitems' => 1,
                'maxitems' => 1,
            ],
        ],
        'participant' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.registration.participant',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_menscircle_domain_model_participant',
                'minitems' => 1,
                'maxitems' => 1,
            ],
        ],
        'status' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.registration.status',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.registration.status.registered', 'value' => 'registered'],
                    ['label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.registration.status.attended', 'value' => 'attended'],
                    ['label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.registration.status.cancelled', 'value' => 'cancelled'],
                ],
                'default' => 'registered',
            ],
        ],
        'registered_at' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.registration.registered_at',
            'config' => [
                'type' => 'datetime',
                'default' => time(),
            ],
        ],
        'cancelled_at' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.registration.cancelled_at',
            'config' => [
                'type' => 'datetime',
                'nullable' => true,
                'default' => null,
            ],
        ],
    ],
];
