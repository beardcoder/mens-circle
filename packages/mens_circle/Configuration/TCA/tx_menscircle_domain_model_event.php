<?php

declare(strict_types=1);

return [
    'ctrl' => [
        'title' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.event.plural',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'sortby' => 'event_date',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'title,slug,location,city',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'iconfile' => 'EXT:mens_circle/Resources/Public/Icons/Record/event.svg',
    ],
    'types' => [
        '1' => [
            'showitem' => '
                --palette--;;date,
                title, slug, teaser, description,
                --div--;Termin,
                    location, street, postal_code, city, location_details,
                    max_participants, cost_basis, is_published,
                    image,
                --div--;Anmeldungen,
                    registrations
            ',
        ],
    ],
    'palettes' => [
        'language' => [
            'showitem' => 'sys_language_uid, l10n_parent',
        ],
        'date' => [
            'showitem' => 'event_date, start_time, end_time',
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
        'title' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.event.title',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'required' => true,
                'eval' => 'trim',
            ],
        ],
        'slug' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.event.slug',
            'config' => [
                'type' => 'slug',
                'generatorOptions' => [
                    'fields' => ['title'],
                    'replacements' => [
                        '/' => '-',
                    ],
                ],
                'fallbackCharacter' => '-',
                'eval' => 'uniqueInSite',
            ],
        ],
        'teaser' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.event.teaser',
            'config' => [
                'type' => 'text',
                'rows' => 3,
            ],
        ],
        'description' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.event.description',
            'config' => [
                'type' => 'text',
                'enableRichtext' => true,
                'rows' => 10,
            ],
        ],
        'event_date' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.event.event_date',
            'config' => [
                'type' => 'datetime',
                'format' => 'date',
                'required' => true,
                'default' => time(),
            ],
        ],
        'start_time' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.event.start_time',
            'config' => [
                'type' => 'datetime',
                'format' => 'time',
                'default' => null,
                'nullable' => true,
            ],
        ],
        'end_time' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.event.end_time',
            'config' => [
                'type' => 'datetime',
                'format' => 'time',
                'default' => null,
                'nullable' => true,
            ],
        ],
        'location' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.event.location',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'eval' => 'trim',
                'required' => true,
            ],
        ],
        'street' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.event.street',
            'config' => [
                'type' => 'input',
                'size' => 40,
                'eval' => 'trim',
            ],
        ],
        'postal_code' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.event.postal_code',
            'config' => [
                'type' => 'input',
                'size' => 10,
                'eval' => 'trim',
            ],
        ],
        'city' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.event.city',
            'config' => [
                'type' => 'input',
                'size' => 40,
                'eval' => 'trim',
            ],
        ],
        'location_details' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.event.location_details',
            'config' => [
                'type' => 'text',
                'rows' => 4,
            ],
        ],
        'max_participants' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.event.max_participants',
            'config' => [
                'type' => 'number',
                'default' => 20,
                'range' => [
                    'lower' => 1,
                    'upper' => 999,
                ],
            ],
        ],
        'cost_basis' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.event.cost_basis',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'eval' => 'trim',
                'default' => 'auf Spendenbasis',
            ],
        ],
        'is_published' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.event.is_published',
            'config' => [
                'type' => 'check',
                'default' => 0,
            ],
        ],
        'image' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.event.image',
            'config' => [
                'type' => 'file',
                'allowed' => 'common-image-types',
                'maxitems' => 1,
            ],
        ],
        'registrations' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.event.registrations',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_menscircle_domain_model_registration',
                'foreign_field' => 'event',
                'maxitems' => 999,
                'appearance' => [
                    'collapseAll' => true,
                    'newRecordLinkAddTitle' => true,
                ],
            ],
        ],
    ],
];
