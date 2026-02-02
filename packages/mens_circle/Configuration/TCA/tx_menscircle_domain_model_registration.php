<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_registration',
        'label' => 'first_name',
        'label_alt' => 'last_name,email',
        'label_alt_Max' => 60,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'iconfile' => 'EXT:mens_circle/Resources/Public/Icons/registration.svg',
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
                'foreign_table_where' => 'AND is_published = 1 ORDER BY event_date DESC',
                'required' => true,
            ],
        ],
        'first_name' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_registration.first_name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
                'required' => true,
            ],
        ],
        'last_name' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_registration.last_name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
                'required' => true,
            ],
        ],
        'email' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_registration.email',
            'config' => [
                'type' => 'email',
                'required' => true,
            ],
        ],
        'phone' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_registration.phone',
            'config' => [
                'type' => 'input',
                'size' => 20,
                'max' => 50,
                'searchable' => false,
            ],
        ],
        'is_confirmed' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_registration.is_confirmed',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'default' => 0,
                'readOnly' => true,
            ],
        ],
        'notes' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_registration.notes',
            'config' => [
                'type' => 'text',
                'rows' => 5,
                'searchable' => false,
            ],
        ],
    ],
    'types' => [
        '1' => [
            'showitem' => '
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                    event, first_name, last_name, email, phone, is_confirmed, notes
            ',
        ],
    ],
];
