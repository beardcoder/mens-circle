<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Schema\Struct\SelectItem;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

// =============================================================================
// Extbase Plugins
// =============================================================================
ExtensionManagementUtility::addPlugin(
    new SelectItem(
        'select',
        'mens_circle.locallang:plugin.events',
        'mens_circle_events',
        'tx-menscircle-event',
    ),
);

ExtensionManagementUtility::addPlugin(
    new SelectItem(
        'select',
        'mens_circle.locallang:plugin.newsletter',
        'mens_circle_newsletter',
        'tx-menscircle-newsletter',
    ),
);

ExtensionManagementUtility::addPlugin(
    new SelectItem(
        'select',
        'mens_circle.locallang:plugin.testimonials',
        'mens_circle_testimonials',
        'tx-menscircle-testimonials',
    ),
);

// =============================================================================
// Register Content Element Group
// =============================================================================
ExtensionManagementUtility::addTcaSelectItemGroup(
    'tt_content',
    'CType',
    'menscircle',
    'mens_circle.backend_fields:tt_content.group.menscircle',
    'after:default'
);
