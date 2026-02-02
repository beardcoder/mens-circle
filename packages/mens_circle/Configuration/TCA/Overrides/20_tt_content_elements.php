<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

// =============================================================================
// Hero Content Element
// =============================================================================
ExtensionManagementUtility::addRecordType(
    [
        'label' => 'mens_circle.backend_fields:tt_content.CType.menscircle_hero.label',
        'description' => 'mens_circle.backend_fields:tt_content.CType.menscircle_hero.description',
        'value' => 'menscircle_hero',
        'icon' => 'tx-menscircle-content-hero',
        'group' => 'menscircle',
    ],
    '
        --div--;core.locallang_core:form.tabs.general,
            --palette--;;general,
            header;mens_circle.backend_fields:tt_content.hero.label,
            subheader;mens_circle.backend_fields:tt_content.hero.title,
            bodytext;mens_circle.backend_fields:tt_content.hero.description,
            pi_flexform,
        --div--;core.locallang_core:form.tabs.media,
            image,
        --div--;core.locallang_core:form.tabs.access,
            --palette--;;hidden,
            --palette--;;access
    ',
    [
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
    ],
);

// =============================================================================
// CTA Content Element
// =============================================================================
ExtensionManagementUtility::addRecordType(
    [
        'label' => 'mens_circle.backend_fields:tt_content.CType.menscircle_cta.label',
        'description' => 'mens_circle.backend_fields:tt_content.CType.menscircle_cta.description',
        'value' => 'menscircle_cta',
        'icon' => 'tx-menscircle-content-cta',
        'group' => 'menscircle',
    ],
    '
        --div--;core.locallang_core:form.tabs.general,
            --palette--;;general,
            header;mens_circle.backend_fields:tt_content.cta.eyebrow,
            subheader;mens_circle.backend_fields:tt_content.cta.title,
            bodytext;mens_circle.backend_fields:tt_content.cta.text,
            pi_flexform,
        --div--;core.locallang_core:form.tabs.access,
            --palette--;;hidden,
            --palette--;;access
    ',
    [
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
    ],
);

// =============================================================================
// FAQ Content Element
// =============================================================================
ExtensionManagementUtility::addRecordType(
    [
        'label' => 'mens_circle.backend_fields:tt_content.CType.menscircle_faq.label',
        'description' => 'mens_circle.backend_fields:tt_content.CType.menscircle_faq.description',
        'value' => 'menscircle_faq',
        'icon' => 'content-menu-abstract',
        'group' => 'menscircle',
    ],
    '
        --div--;core.locallang_core:form.tabs.general,
            --palette--;;general,
            header;mens_circle.backend_fields:tt_content.faq.eyebrow,
            subheader;mens_circle.backend_fields:tt_content.faq.title,
            bodytext;mens_circle.backend_fields:tt_content.faq.intro,
            pi_flexform,
        --div--;core.locallang_core:form.tabs.access,
            --palette--;;hidden,
            --palette--;;access
    ',
    [
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
    ],
);

// =============================================================================
// Intro Content Element
// =============================================================================
ExtensionManagementUtility::addRecordType(
    [
        'label' => 'mens_circle.backend_fields:tt_content.CType.menscircle_intro.label',
        'description' => 'mens_circle.backend_fields:tt_content.CType.menscircle_intro.description',
        'value' => 'menscircle_intro',
        'icon' => 'content-text-columns',
        'group' => 'menscircle',
    ],
    '
        --div--;core.locallang_core:form.tabs.general,
            --palette--;;general,
            header;mens_circle.backend_fields:tt_content.intro.eyebrow,
            subheader;mens_circle.backend_fields:tt_content.intro.title,
            bodytext;mens_circle.backend_fields:tt_content.intro.text,
            pi_flexform,
        --div--;core.locallang_core:form.tabs.access,
            --palette--;;hidden,
            --palette--;;access
    ',
    [
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
    ],
);

// =============================================================================
// Journey Steps Content Element
// =============================================================================
ExtensionManagementUtility::addRecordType(
    [
        'label' => 'mens_circle.backend_fields:tt_content.CType.menscircle_journey.label',
        'description' => 'mens_circle.backend_fields:tt_content.CType.menscircle_journey.description',
        'value' => 'menscircle_journey',
        'icon' => 'content-menu-section',
        'group' => 'menscircle',
    ],
    '
        --div--;core.locallang_core:form.tabs.general,
            --palette--;;general,
            header;mens_circle.backend_fields:tt_content.journey.eyebrow,
            subheader;mens_circle.backend_fields:tt_content.journey.title,
            bodytext;mens_circle.backend_fields:tt_content.journey.subtitle,
            pi_flexform,
        --div--;core.locallang_core:form.tabs.access,
            --palette--;;hidden,
            --palette--;;access
    ',
    [
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
    ],
);

// =============================================================================
// Moderator Content Element
// =============================================================================
ExtensionManagementUtility::addRecordType(
    [
        'label' => 'mens_circle.backend_fields:tt_content.CType.menscircle_moderator.label',
        'description' => 'mens_circle.backend_fields:tt_content.CType.menscircle_moderator.description',
        'value' => 'menscircle_moderator',
        'icon' => 'content-special-user_links',
        'group' => 'menscircle',
    ],
    '
        --div--;core.locallang_core:form.tabs.general,
            --palette--;;general,
            header;mens_circle.backend_fields:tt_content.moderator.eyebrow,
            subheader;mens_circle.backend_fields:tt_content.moderator.name,
            bodytext;mens_circle.backend_fields:tt_content.moderator.bio,
            pi_flexform,
        --div--;core.locallang_core:form.tabs.media,
            image,
        --div--;core.locallang_core:form.tabs.access,
            --palette--;;hidden,
            --palette--;;access
    ',
    [
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
    ],
);

// =============================================================================
// Text Section Content Element
// =============================================================================
ExtensionManagementUtility::addRecordType(
    [
        'label' => 'mens_circle.backend_fields:tt_content.CType.menscircle_textsection.label',
        'description' => 'mens_circle.backend_fields:tt_content.CType.menscircle_textsection.description',
        'value' => 'menscircle_textsection',
        'icon' => 'content-text',
        'group' => 'menscircle',
    ],
    '
        --div--;core.locallang_core:form.tabs.general,
            --palette--;;general,
            header;mens_circle.backend_fields:tt_content.textsection.eyebrow,
            subheader;mens_circle.backend_fields:tt_content.textsection.title,
            bodytext;mens_circle.backend_fields:tt_content.textsection.content,
        --div--;core.locallang_core:form.tabs.access,
            --palette--;;hidden,
            --palette--;;access
    ',
    [
        'columnsOverrides' => [
            'bodytext' => [
                'config' => [
                    'enableRichtext' => true,
                ],
            ],
        ],
    ],
);

// =============================================================================
// Value Items Content Element
// =============================================================================
ExtensionManagementUtility::addRecordType(
    [
        'label' => 'mens_circle.backend_fields:tt_content.CType.menscircle_values.label',
        'description' => 'mens_circle.backend_fields:tt_content.CType.menscircle_values.description',
        'value' => 'menscircle_values',
        'icon' => 'content-bullets',
        'group' => 'menscircle',
    ],
    '
        --div--;core.locallang_core:form.tabs.general,
            --palette--;;general,
            header;mens_circle.backend_fields:tt_content.values.eyebrow,
            subheader;mens_circle.backend_fields:tt_content.values.title,
            pi_flexform,
        --div--;core.locallang_core:form.tabs.access,
            --palette--;;hidden,
            --palette--;;access
    ',
    [
        'columnsOverrides' => [
            'pi_flexform' => [
                'config' => [
                    'ds' => [
                        'default' => 'FILE:EXT:mens_circle/Configuration/FlexForms/Values.xml',
                    ],
                ],
            ],
        ],
    ],
);

// =============================================================================
// WhatsApp Community Content Element
// =============================================================================
ExtensionManagementUtility::addRecordType(
    [
        'label' => 'mens_circle.backend_fields:tt_content.CType.menscircle_whatsapp.label',
        'description' => 'mens_circle.backend_fields:tt_content.CType.menscircle_whatsapp.description',
        'value' => 'menscircle_whatsapp',
        'icon' => 'content-special-uploads',
        'group' => 'menscircle',
    ],
    '
        --div--;core.locallang_core:form.tabs.general,
            --palette--;;general,
            header;mens_circle.backend_fields:tt_content.whatsapp.eyebrow,
            subheader;mens_circle.backend_fields:tt_content.whatsapp.title,
            bodytext;mens_circle.backend_fields:tt_content.whatsapp.text,
            pi_flexform,
        --div--;core.locallang_core:form.tabs.access,
            --palette--;;hidden,
            --palette--;;access
    ',
    [
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
    ],
);

// =============================================================================
// Newsletter Section Content Element
// =============================================================================
ExtensionManagementUtility::addRecordType(
    [
        'label' => 'mens_circle.backend_fields:tt_content.CType.menscircle_newsletter_section.label',
        'description' => 'mens_circle.backend_fields:tt_content.CType.menscircle_newsletter_section.description',
        'value' => 'menscircle_newsletter_section',
        'icon' => 'content-form',
        'group' => 'menscircle',
    ],
    '
        --div--;core.locallang_core:form.tabs.general,
            --palette--;;general,
            header;mens_circle.backend_fields:tt_content.newsletter_section.eyebrow,
            subheader;mens_circle.backend_fields:tt_content.newsletter_section.title,
            bodytext;mens_circle.backend_fields:tt_content.newsletter_section.text,
        --div--;core.locallang_core:form.tabs.access,
            --palette--;;hidden,
            --palette--;;access
    ',
    [
        'columnsOverrides' => [
            'bodytext' => [
                'config' => [
                    'enableRichtext' => false,
                ],
            ],
        ],
    ],
);

// =============================================================================
// Testimonials Section Content Element
// =============================================================================
ExtensionManagementUtility::addRecordType(
    [
        'label' => 'mens_circle.backend_fields:tt_content.CType.menscircle_testimonials_section.label',
        'description' => 'mens_circle.backend_fields:tt_content.CType.menscircle_testimonials_section.description',
        'value' => 'menscircle_testimonials_section',
        'icon' => 'content-quote',
        'group' => 'menscircle',
    ],
    '
        --div--;core.locallang_core:form.tabs.general,
            --palette--;;general,
            header;mens_circle.backend_fields:tt_content.testimonials_section.eyebrow,
            subheader;mens_circle.backend_fields:tt_content.testimonials_section.title,
            bodytext;mens_circle.backend_fields:tt_content.testimonials_section.subtitle,
        --div--;core.locallang_core:form.tabs.access,
            --palette--;;hidden,
            --palette--;;access
    ',
    [
        'columnsOverrides' => [
            'bodytext' => [
                'config' => [
                    'enableRichtext' => false,
                ],
            ],
        ],
    ],
);
