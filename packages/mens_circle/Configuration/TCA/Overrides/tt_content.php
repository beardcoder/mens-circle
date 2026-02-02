<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Schema\Struct\SelectItem;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

$ll = 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:';

// =============================================================================
// Extbase Plugins
// =============================================================================
ExtensionManagementUtility::addPlugin(
    new SelectItem(
        'select',
        'LLL:EXT:mens_circle/Resources/Private/Language/locallang.xlf:plugin.events',
        'mens_circle_events',
        'tx-menscircle-event',
    ),
);

ExtensionManagementUtility::addPlugin(
    new SelectItem(
        'select',
        'LLL:EXT:mens_circle/Resources/Private/Language/locallang.xlf:plugin.newsletter',
        'mens_circle_newsletter',
        'tx-menscircle-newsletter',
    ),
);

ExtensionManagementUtility::addPlugin(
    new SelectItem(
        'select',
        'LLL:EXT:mens_circle/Resources/Private/Language/locallang.xlf:plugin.testimonials',
        'mens_circle_testimonials',
        'tx-menscircle-testimonials',
    ),
);

// =============================================================================
// Custom Content Elements
// =============================================================================

// Hero Content Element
ExtensionManagementUtility::addTcaSelectItem(
    'tt_content',
    'CType',
    new SelectItem(
        'select',
        'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.CType.menscircle_hero',
        'menscircle_hero',
        'tx-menscircle-content-hero',
        'menscircle'
    ),
);

$GLOBALS['TCA']['tt_content']['types']['menscircle_hero'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            header;LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.hero.label,
            subheader;LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.hero.title,
            bodytext;LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.hero.description,
            pi_flexform,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:media,
            image,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
            --palette--;;hidden,
            --palette--;;access,
    ',
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => false,
            ],
        ],
        'image' => [
            'config' => [
                'maxitems' => 1,
            ],
        ],
        'pi_flexform' => [
            'config' => [
                'ds' => [
                    'default' => 'FILE:EXT:mens_circle/Configuration/FlexForms/Hero.xml',
                ],
            ],
        ],
    ],
];

// CTA Content Element
ExtensionManagementUtility::addTcaSelectItem(
    'tt_content',
    'CType',
    new SelectItem(
        'select',
        'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.CType.menscircle_cta',
        'menscircle_cta',
        'tx-menscircle-content-cta',
        'menscircle'
    ),
);

$GLOBALS['TCA']['tt_content']['types']['menscircle_cta'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            header;LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.cta.eyebrow,
            subheader;LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.cta.title,
            bodytext;LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.cta.text,
            pi_flexform,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
            --palette--;;hidden,
            --palette--;;access,
    ',
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => false,
            ],
        ],
        'pi_flexform' => [
            'config' => [
                'ds' => [
                    'default' => 'FILE:EXT:mens_circle/Configuration/FlexForms/Cta.xml',
                ],
            ],
        ],
    ],
];

// FAQ Content Element
ExtensionManagementUtility::addTcaSelectItem(
    'tt_content',
    'CType',
    [
        'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.CType.menscircle_faq',
        'value' => 'menscircle_faq',
        'icon' => 'content-menu-abstract',
        'group' => 'menscircle',
    ],
);

$GLOBALS['TCA']['tt_content']['types']['menscircle_faq'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            header;LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.faq.eyebrow,
            subheader;LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.faq.title,
            bodytext;LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.faq.intro,
            pi_flexform,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
            --palette--;;hidden,
            --palette--;;access,
    ',
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => false,
            ],
        ],
        'pi_flexform' => [
            'config' => [
                'ds' => [
                    'default' => 'FILE:EXT:mens_circle/Configuration/FlexForms/Faq.xml',
                ],
            ],
        ],
    ],
];

// Intro Content Element
ExtensionManagementUtility::addTcaSelectItem(
    'tt_content',
    'CType',
    [
        'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.CType.menscircle_intro',
        'value' => 'menscircle_intro',
        'icon' => 'content-text-columns',
        'group' => 'menscircle',
    ],
);

$GLOBALS['TCA']['tt_content']['types']['menscircle_intro'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            header;LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.intro.eyebrow,
            subheader;LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.intro.title,
            bodytext;LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.intro.text,
            pi_flexform,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
            --palette--;;hidden,
            --palette--;;access,
    ',
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => false,
            ],
        ],
        'pi_flexform' => [
            'config' => [
                'ds' => [
                    'default' => 'FILE:EXT:mens_circle/Configuration/FlexForms/Intro.xml',
                ],
            ],
        ],
    ],
];

// Journey Steps Content Element
ExtensionManagementUtility::addTcaSelectItem(
    'tt_content',
    'CType',
    [
        'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.CType.menscircle_journey',
        'value' => 'menscircle_journey',
        'icon' => 'content-menu-section',
        'group' => 'menscircle',
    ],
);

$GLOBALS['TCA']['tt_content']['types']['menscircle_journey'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            header;LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.journey.eyebrow,
            subheader;LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.journey.title,
            bodytext;LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.journey.subtitle,
            pi_flexform,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
            --palette--;;hidden,
            --palette--;;access,
    ',
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => false,
            ],
        ],
        'pi_flexform' => [
            'config' => [
                'ds' => [
                    'default' => 'FILE:EXT:mens_circle/Configuration/FlexForms/Journey.xml',
                ],
            ],
        ],
    ],
];

// Moderator Content Element
ExtensionManagementUtility::addTcaSelectItem(
    'tt_content',
    'CType',
    [
        'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.CType.menscircle_moderator',
        'value' => 'menscircle_moderator',
        'icon' => 'content-special-user_links',
        'group' => 'menscircle',
    ],
);

$GLOBALS['TCA']['tt_content']['types']['menscircle_moderator'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            header;LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.moderator.eyebrow,
            subheader;LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.moderator.name,
            bodytext;LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.moderator.bio,
            pi_flexform,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:media,
            image,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
            --palette--;;hidden,
            --palette--;;access,
    ',
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
            ],
        ],
        'image' => [
            'config' => [
                'maxitems' => 1,
            ],
        ],
        'pi_flexform' => [
            'config' => [
                'ds' => [
                    'default' => 'FILE:EXT:mens_circle/Configuration/FlexForms/Moderator.xml',
                ],
            ],
        ],
    ],
];

// Text Section Content Element
ExtensionManagementUtility::addTcaSelectItem(
    'tt_content',
    'CType',
    [
        'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.CType.menscircle_textsection',
        'value' => 'menscircle_textsection',
        'icon' => 'content-text',
        'group' => 'menscircle',
    ],
);

$GLOBALS['TCA']['tt_content']['types']['menscircle_textsection'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            header;LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.textsection.eyebrow,
            subheader;LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.textsection.title,
            bodytext;LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.textsection.content,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
            --palette--;;hidden,
            --palette--;;access,
    ',
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
            ],
        ],
    ],
];

// Value Items Content Element
ExtensionManagementUtility::addTcaSelectItem(
    'tt_content',
    'CType',
    [
        'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.CType.menscircle_values',
        'value' => 'menscircle_values',
        'icon' => 'content-bullets',
        'group' => 'menscircle',
    ],
);

$GLOBALS['TCA']['tt_content']['types']['menscircle_values'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            header;LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.values.eyebrow,
            subheader;LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.values.title,
            pi_flexform,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
            --palette--;;hidden,
            --palette--;;access,
    ',
    'columnsOverrides' => [
        'pi_flexform' => [
            'config' => [
                'ds' => [
                    'default' => 'FILE:EXT:mens_circle/Configuration/FlexForms/Values.xml',
                ],
            ],
        ],
    ],
];

// WhatsApp Community Content Element
ExtensionManagementUtility::addTcaSelectItem(
    'tt_content',
    'CType',
    [
        'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.CType.menscircle_whatsapp',
        'value' => 'menscircle_whatsapp',
        'icon' => 'content-special-uploads',
        'group' => 'menscircle',
    ],
);

$GLOBALS['TCA']['tt_content']['types']['menscircle_whatsapp'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            header;LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.whatsapp.eyebrow,
            subheader;LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.whatsapp.title,
            bodytext;LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.whatsapp.text,
            pi_flexform,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
            --palette--;;hidden,
            --palette--;;access,
    ',
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => false,
            ],
        ],
        'pi_flexform' => [
            'config' => [
                'ds' => [
                    'default' => 'FILE:EXT:mens_circle/Configuration/FlexForms/WhatsApp.xml',
                ],
            ],
        ],
    ],
];

// Newsletter Section Content Element
ExtensionManagementUtility::addTcaSelectItem(
    'tt_content',
    'CType',
    [
        'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.CType.menscircle_newsletter_section',
        'value' => 'menscircle_newsletter_section',
        'icon' => 'content-form',
        'group' => 'menscircle',
    ],
);

$GLOBALS['TCA']['tt_content']['types']['menscircle_newsletter_section'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            header;LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.newsletter_section.eyebrow,
            subheader;LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.newsletter_section.title,
            bodytext;LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.newsletter_section.text,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
            --palette--;;hidden,
            --palette--;;access,
    ',
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => false,
            ],
        ],
    ],
];

// Testimonials Section Content Element
ExtensionManagementUtility::addTcaSelectItem(
    'tt_content',
    'CType',
    [
        'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.CType.menscircle_testimonials_section',
        'value' => 'menscircle_testimonials_section',
        'icon' => 'content-quote',
        'group' => 'menscircle',
    ],
);

$GLOBALS['TCA']['tt_content']['types']['menscircle_testimonials_section'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            header;LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.testimonials_section.eyebrow,
            subheader;LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.testimonials_section.title,
            bodytext;LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.testimonials_section.subtitle,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
            --palette--;;hidden,
            --palette--;;access,
    ',
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => false,
            ],
        ],
    ],
];

// =============================================================================
// Register Content Element Group
// =============================================================================
ExtensionManagementUtility::addTcaSelectItemGroup(
    'tt_content',
    'CType',
    'menscircle',
    'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.CType.group.menscircle',
);
