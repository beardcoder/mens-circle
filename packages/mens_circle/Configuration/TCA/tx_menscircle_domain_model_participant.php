<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_participant',
        'label' => 'first_name',
        'label_alt' => 'last_name',
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
        'registration' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_participant.registration',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [],
                'foreign_table' => 'tx_menscircle_domain_model_registration',
                'readOnly' => true,
            ],
        ],
        'first_name' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_participant.first_name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
                'required' => true,
            ],
        ],
        'last_name' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_participant.last_name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
                'required' => true,
            ],
        ],
        'email' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_participant.email',
            'config' => [
                'type' => 'email',
            ],
        ],
        'phone' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_participant.phone',
            'config' => [
                'type' => 'input',
                'size' => 20,
                'max' => 50,
                'searchable' => false,
            ],
        ],
    ],
    'types' => [
        '1' => [
            'showitem' => '
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                    registration, first_name, last_name, email, phone
            ',
        ],
    ],
];
