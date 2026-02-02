<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_testimonial',
        'label' => 'author_name',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'iconfile' => 'EXT:mens_circle/Resources/Public/Icons/testimonial.svg',
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
    ],
    'columns' => [
        'author_name' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_testimonial.author_name',
            'config' => [
                'type' => 'input',
                'size' => 40,
                'max' => 255,
                'required' => true,
            ],
        ],
        'role' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_testimonial.role',
            'config' => [
                'type' => 'input',
                'size' => 40,
                'max' => 255,
                'placeholder' => 'z.B. Teilnehmer seit 2023',
            ],
        ],
        'content' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_testimonial.content',
            'config' => [
                'type' => 'text',
                'rows' => 8,
                'required' => true,
            ],
        ],
        'event' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_testimonial.event',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [['label' => '', 'value' => 0]],
                'foreign_table' => 'tx_menscircle_domain_model_event',
                'foreign_table_where' => 'ORDER BY event_date DESC',
            ],
        ],
        'is_approved' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_testimonial.is_approved',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'default' => 0,
            ],
        ],
    ],
    'types' => [
        '1' => [
            'showitem' => '
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                    author_name, role, content, event, is_approved
            ',
        ],
    ],
];
