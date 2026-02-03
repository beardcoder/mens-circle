<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_newsletter',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'iconfile' => 'EXT:mens_circle/Resources/Public/Icons/newsletter.svg',
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
    ],
    'columns' => [
        'title' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_newsletter.title',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'max' => 255,
                'required' => true,
            ],
        ],
        'subject' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_newsletter.subject',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'max' => 255,
                'required' => true,
            ],
        ],
        'content' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_newsletter.content',
            'config' => [
                'type' => 'text',
                'enableRichtext' => true,
                'rows' => 20,
                'searchable' => false,
            ],
        ],
        'is_sent' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_newsletter.is_sent',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'default' => 0,
                'readOnly' => true,
            ],
        ],
        'sent_at' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_newsletter.sent_at',
            'config' => [
                'type' => 'datetime',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'recipient_count' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_newsletter.recipient_count',
            'config' => [
                'type' => 'number',
                'default' => 0,
                'readOnly' => true,
            ],
        ],
    ],
    'types' => [
        '1' => [
            'showitem' => '
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                    title, subject, content, is_sent, sent_at, recipient_count
            ',
        ],
    ],
];
