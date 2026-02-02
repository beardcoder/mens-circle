<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_registration',
        'label' => 'participant',
        'label_alt' => 'event',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'iconfile' => 'EXT:mens_circle/Resources/Public/Icons/registration.svg',
        'searchFields' => 'notes',
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
    ],
    'columns' => [
        'event' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_registration.event',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [],
                'foreign_table' => 'tx_menscircle_domain_model_event',
                'foreign_table_where' => 'AND {#tx_menscircle_domain_model_event}.{#is_published} = 1 ORDER BY {#tx_menscircle_domain_model_event}.{#event_date} DESC',
                'required' => true,
            ],
        ],
        'participant' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_registration.participant',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [],
                'foreign_table' => 'tx_menscircle_domain_model_participant',
                'required' => true,
            ],
        ],
        'is_confirmed' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_registration.is_confirmed',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'default' => 0,
            ],
        ],
        'confirmation_token' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_registration.confirmation_token',
            'config' => [
                'type' => 'input',
                'size' => 64,
                'max' => 64,
                'readOnly' => true,
            ],
        ],
        'notes' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_registration.notes',
            'config' => [
                'type' => 'text',
                'rows' => 5,
                'eval' => 'trim',
            ],
        ],
        'created_at' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_registration.created_at',
            'config' => [
                'type' => 'datetime',
                'format' => 'datetime',
                'readOnly' => true,
            ],
        ],
        'confirmed_at' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_registration.confirmed_at',
            'config' => [
                'type' => 'datetime',
                'format' => 'datetime',
                'readOnly' => true,
            ],
        ],
    ],
    'types' => [
        '1' => [
            'showitem' => '
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                    event, participant, is_confirmed, confirmed_at, notes,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                    created_at, confirmation_token
            ',
        ],
    ],
];
