<?php

declare(strict_types=1);

return [
    'ctrl' => [
        'title' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.testimonial.plural',
        'label' => 'author_name',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'sortby' => 'sort_order',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'quote,author_name,email,role',
        'iconfile' => 'EXT:mens_circle/Resources/Public/Icons/Record/testimonial.svg',
    ],
    'types' => [
        '1' => [
            'showitem' => 'hidden, quote, author_name, email, role, is_published, published_at, sort_order',
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
        'quote' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.testimonial.quote',
            'config' => [
                'type' => 'text',
                'required' => true,
                'rows' => 6,
            ],
        ],
        'author_name' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.testimonial.author_name',
            'config' => [
                'type' => 'input',
                'size' => 40,
                'eval' => 'trim',
            ],
        ],
        'email' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.testimonial.email',
            'config' => [
                'type' => 'input',
                'size' => 40,
                'required' => true,
                'eval' => 'trim,lower,email',
            ],
        ],
        'role' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.testimonial.role',
            'config' => [
                'type' => 'input',
                'size' => 40,
                'eval' => 'trim',
            ],
        ],
        'is_published' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.testimonial.is_published',
            'config' => [
                'type' => 'check',
                'default' => 0,
            ],
        ],
        'published_at' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.testimonial.published_at',
            'config' => [
                'type' => 'datetime',
                'nullable' => true,
                'default' => null,
            ],
        ],
        'sort_order' => [
            'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:table.testimonial.sort_order',
            'config' => [
                'type' => 'number',
                'default' => 0,
                'range' => [
                    'lower' => 0,
                    'upper' => 9999,
                ],
            ],
        ],
    ],
];
