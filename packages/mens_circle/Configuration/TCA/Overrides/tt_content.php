<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

ExtensionManagementUtility::addTcaSelectItemGroup(
    'tt_content',
    'CType',
    'menscircle',
    'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:content.group.title',
    'after:default'
);

$commonTabs = '
    --div--;core.form.tabs:appearance,
    --palette--;;frames,
    --div--;core.form.tabs:language,
    --palette--;;language,
    --div--;core.form.tabs:access,
    --palette--;;hidden,
    --palette--;;access
';

$contentTypes = [
    'menscircle_hero' => [
        'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:content.type.hero.title',
        'description' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:content.type.hero.description',
        'icon' => 'content-menscircle-hero',
        'showitem' => '
            --palette--;;headers,
            bodytext,
            header_link,
            --div--;core.form.tabs:images,
            image,
            --div--;core.form.tabs:plugin,
            pi_flexform,
        ',
        'columnsOverrides' => [
            'bodytext' => [
                'config' => [
                    'enableRichtext' => true,
                    'rows' => 6,
                ],
            ],
            'image' => [
                'config' => [
                    'maxitems' => 1,
                ],
            ],
            'pi_flexform' => [
                'config' => [
                    'ds' => 'FILE:EXT:mens_circle/Configuration/FlexForms/ContentElements/Hero.xml',
                ],
            ],
        ],
    ],
    'menscircle_intro' => [
        'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:content.type.intro.title',
        'description' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:content.type.intro.description',
        'icon' => 'content-menscircle-intro',
        'showitem' => '
            --palette--;;headers,
            bodytext,
            --div--;core.form.tabs:plugin,
            pi_flexform,
        ',
        'columnsOverrides' => [
            'bodytext' => [
                'config' => [
                    'enableRichtext' => true,
                    'rows' => 6,
                ],
            ],
            'pi_flexform' => [
                'config' => [
                    'ds' => 'FILE:EXT:mens_circle/Configuration/FlexForms/ContentElements/Intro.xml',
                ],
            ],
        ],
    ],
    'menscircle_text_section' => [
        'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:content.type.text_section.title',
        'description' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:content.type.text_section.description',
        'icon' => 'content-menscircle-text-section',
        'showitem' => '
            --palette--;;headers,
            bodytext,
        ',
        'columnsOverrides' => [
            'bodytext' => [
                'config' => [
                    'enableRichtext' => true,
                    'rows' => 8,
                ],
            ],
        ],
    ],
    'menscircle_value_items' => [
        'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:content.type.value_items.title',
        'description' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:content.type.value_items.description',
        'icon' => 'content-menscircle-value-items',
        'showitem' => '
            --palette--;;headers,
            bodytext,
            --div--;core.form.tabs:plugin,
            pi_flexform,
        ',
        'columnsOverrides' => [
            'bodytext' => [
                'config' => [
                    'enableRichtext' => true,
                    'rows' => 4,
                ],
            ],
            'pi_flexform' => [
                'config' => [
                    'ds' => 'FILE:EXT:mens_circle/Configuration/FlexForms/ContentElements/ValueItems.xml',
                ],
            ],
        ],
    ],
    'menscircle_moderator' => [
        'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:content.type.moderator.title',
        'description' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:content.type.moderator.description',
        'icon' => 'content-menscircle-moderator',
        'showitem' => '
            --palette--;;headers,
            bodytext,
            --div--;core.form.tabs:images,
            image,
            --div--;core.form.tabs:plugin,
            pi_flexform,
        ',
        'columnsOverrides' => [
            'bodytext' => [
                'config' => [
                    'enableRichtext' => true,
                    'rows' => 8,
                ],
            ],
            'image' => [
                'config' => [
                    'maxitems' => 1,
                ],
            ],
            'pi_flexform' => [
                'config' => [
                    'ds' => 'FILE:EXT:mens_circle/Configuration/FlexForms/ContentElements/Moderator.xml',
                ],
            ],
        ],
    ],
    'menscircle_journey_steps' => [
        'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:content.type.journey_steps.title',
        'description' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:content.type.journey_steps.description',
        'icon' => 'content-menscircle-journey-steps',
        'showitem' => '
            --palette--;;headers,
            bodytext,
            --div--;core.form.tabs:plugin,
            pi_flexform,
        ',
        'columnsOverrides' => [
            'bodytext' => [
                'config' => [
                    'enableRichtext' => true,
                    'rows' => 5,
                ],
            ],
            'pi_flexform' => [
                'config' => [
                    'ds' => 'FILE:EXT:mens_circle/Configuration/FlexForms/ContentElements/JourneySteps.xml',
                ],
            ],
        ],
    ],
    'menscircle_testimonials' => [
        'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:content.type.testimonials.title',
        'description' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:content.type.testimonials.description',
        'icon' => 'content-menscircle-testimonials',
        'showitem' => '
            --palette--;;headers,
            bodytext,
            --div--;core.form.tabs:plugin,
            pi_flexform,
        ',
        'columnsOverrides' => [
            'bodytext' => [
                'config' => [
                    'enableRichtext' => true,
                    'rows' => 4,
                ],
            ],
            'pi_flexform' => [
                'config' => [
                    'ds' => 'FILE:EXT:mens_circle/Configuration/FlexForms/ContentElements/Testimonials.xml',
                ],
            ],
        ],
    ],
    'menscircle_faq' => [
        'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:content.type.faq.title',
        'description' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:content.type.faq.description',
        'icon' => 'content-menscircle-faq',
        'showitem' => '
            --palette--;;headers,
            bodytext,
            --div--;core.form.tabs:plugin,
            pi_flexform,
        ',
        'columnsOverrides' => [
            'bodytext' => [
                'config' => [
                    'enableRichtext' => true,
                    'rows' => 4,
                ],
            ],
            'pi_flexform' => [
                'config' => [
                    'ds' => 'FILE:EXT:mens_circle/Configuration/FlexForms/ContentElements/Faq.xml',
                ],
            ],
        ],
    ],
    'menscircle_newsletter_section' => [
        'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:content.type.newsletter.title',
        'description' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:content.type.newsletter.description',
        'icon' => 'content-menscircle-newsletter',
        'showitem' => '
            --palette--;;headers,
            bodytext,
            header_link,
            --div--;core.form.tabs:plugin,
            pi_flexform,
        ',
        'columnsOverrides' => [
            'bodytext' => [
                'config' => [
                    'enableRichtext' => true,
                    'rows' => 5,
                ],
            ],
            'pi_flexform' => [
                'config' => [
                    'ds' => 'FILE:EXT:mens_circle/Configuration/FlexForms/ContentElements/Newsletter.xml',
                ],
            ],
        ],
    ],
    'menscircle_cta' => [
        'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:content.type.cta.title',
        'description' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:content.type.cta.description',
        'icon' => 'content-menscircle-cta',
        'showitem' => '
            --palette--;;headers,
            bodytext,
            header_link,
            --div--;core.form.tabs:plugin,
            pi_flexform,
        ',
        'columnsOverrides' => [
            'bodytext' => [
                'config' => [
                    'enableRichtext' => true,
                    'rows' => 4,
                ],
            ],
            'pi_flexform' => [
                'config' => [
                    'ds' => 'FILE:EXT:mens_circle/Configuration/FlexForms/ContentElements/Cta.xml',
                ],
            ],
        ],
    ],
    'menscircle_whatsapp_community' => [
        'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:content.type.whatsapp_community.title',
        'description' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:content.type.whatsapp_community.description',
        'icon' => 'content-menscircle-whatsapp',
        'showitem' => '
            --palette--;;headers,
            bodytext,
            header_link,
            --div--;core.form.tabs:plugin,
            pi_flexform,
        ',
        'columnsOverrides' => [
            'bodytext' => [
                'config' => [
                    'enableRichtext' => true,
                    'rows' => 4,
                ],
            ],
            'pi_flexform' => [
                'config' => [
                    'ds' => 'FILE:EXT:mens_circle/Configuration/FlexForms/ContentElements/WhatsappCommunity.xml',
                ],
            ],
        ],
    ],
];

foreach ($contentTypes as $contentType => $configuration) {
    ExtensionManagementUtility::addRecordType(
        [
            'label' => $configuration['label'],
            'description' => $configuration['description'],
            'value' => $contentType,
            'icon' => $configuration['icon'],
            'group' => 'menscircle',
        ],
        $configuration['showitem'] . $commonTabs,
        [
            'columnsOverrides' => $configuration['columnsOverrides'],
        ]
    );
}

$pluginSignatures = [
    'event' => ExtensionUtility::registerPlugin(
        'MensCircle',
        'Event',
        'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:plugin.event.title',
        'plugin-menscircle-event',
        'plugins',
        'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:plugin.event.description'
    ),
    'eventDetail' => ExtensionUtility::registerPlugin(
        'MensCircle',
        'EventDetail',
        'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:plugin.event_detail.title',
        'plugin-menscircle-event-detail',
        'plugins',
        'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:plugin.event_detail.description'
    ),
    'newsletter' => ExtensionUtility::registerPlugin(
        'MensCircle',
        'Newsletter',
        'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:plugin.newsletter.title',
        'plugin-menscircle-newsletter',
        'plugins',
        'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:plugin.newsletter.description'
    ),
    'testimonialForm' => ExtensionUtility::registerPlugin(
        'MensCircle',
        'TestimonialForm',
        'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:plugin.testimonial_form.title',
        'plugin-menscircle-testimonial-form',
        'plugins',
        'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:plugin.testimonial_form.description'
    ),
];

foreach ($pluginSignatures as $pluginSignature) {
    $GLOBALS['TCA']['tt_content']['types'][$pluginSignature]['showitem'] = '
        --palette--;;headers,
        pi_flexform,
    ' . $commonTabs;
}

$GLOBALS['TCA']['tt_content']['types'][$pluginSignatures['eventDetail']]['columnsOverrides']['pi_flexform']['config']['ds']
    = 'FILE:EXT:mens_circle/Configuration/FlexForms/Plugins/EventDetail.xml';
