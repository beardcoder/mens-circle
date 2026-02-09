<?php

declare(strict_types=1);

return [
    'ctrl' => [
        'title' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.newslettersubscription.plural',
        'label' => 'token',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'token',
        'iconfile' => 'EXT:mens_circle/Resources/Public/Icons/Record/newsletter-subscription.svg',
    ],
    'types' => [
        '1' => [
            'showitem' => 'hidden, participant, token, subscribed_at, confirmed_at, unsubscribed_at',
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
        'participant' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.newslettersubscription.participant',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_menscircle_domain_model_participant',
                'minitems' => 1,
                'maxitems' => 1,
            ],
        ],
        'token' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.newslettersubscription.token',
            'config' => [
                'type' => 'input',
                'size' => 60,
                'readOnly' => true,
            ],
        ],
        'subscribed_at' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.newslettersubscription.subscribed_at',
            'config' => [
                'type' => 'datetime',
                'default' => time(),
            ],
        ],
        'confirmed_at' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.newslettersubscription.confirmed_at',
            'config' => [
                'type' => 'datetime',
                'nullable' => true,
                'default' => null,
            ],
        ],
        'unsubscribed_at' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.newslettersubscription.unsubscribed_at',
            'config' => [
                'type' => 'datetime',
                'nullable' => true,
                'default' => null,
            ],
        ],
    ],
];
