<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_event',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'sortby' => 'sorting',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'iconfile' => 'EXT:mens_circle/Resources/Public/Icons/event.svg',
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
    ],
    'columns' => [
        'title' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_event.title',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'max' => 255,
                'required' => true,
            ],
        ],
        'slug' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_event.slug',
            'config' => [
                'type' => 'slug',
                'generatorOptions' => [
                    'fields' => ['title'],
                    'fieldSeparator' => '-',
                    'prefixParentPageSlug' => false,
                ],
                'fallbackCharacter' => '-',
                'eval' => 'uniqueInSite',
                'searchable' => false,
            ],
        ],
        'description' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_event.description',
            'config' => [
                'type' => 'text',
                'enableRichtext' => true,
                'rows' => 15,
            ],
        ],
        'location' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_event.location',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'max' => 255,
            ],
        ],
        'event_date' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_event.event_date',
            'config' => [
                'type' => 'datetime',
                'required' => true,
                'searchable' => false,
            ],
        ],
        'event_end_date' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_event.event_end_date',
            'config' => [
                'type' => 'datetime',
                'searchable' => false,
            ],
        ],
        'max_participants' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_event.max_participants',
            'config' => [
                'type' => 'number',
                'default' => 0,
            ],
        ],
        'is_published' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_event.is_published',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'default' => 0,
            ],
        ],
        'event_image' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_event.event_image',
            'config' => [
                'type' => 'file',
                'maxitems' => 1,
                'allowed' => 'common-image-types',
            ],
        ],
        'registrations' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tx_menscircle_domain_model_event.registrations',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_menscircle_domain_model_registration',
                'foreign_field' => 'event',
                'appearance' => [
                    'collapseAll' => true,
                    'expandSingle' => true,
                    'useSortable' => false,
                ],
            ],
        ],
    ],
    'types' => [
        '1' => [
            'showitem' => '
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                    title, slug, description, location,
                --div--;Event Details,
                    event_date, event_end_date, max_participants, is_published, event_image,
                --div--;Registrations,
                    registrations,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                    hidden, starttime, endtime
            ',
        ],
    ],
];
