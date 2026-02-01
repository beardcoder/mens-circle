<?php

TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
    new TYPO3\CMS\Core\Schema\Struct\SelectItem(
        'select',
        'LLL:EXT:mens_circle/Resources/Private/Language/locallang.xlf:plugin.events',
        'mens_circle_events',
        'tx-menscircle-event'
    ),
);

TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
    new TYPO3\CMS\Core\Schema\Struct\SelectItem(
        'select',
        'LLL:EXT:mens_circle/Resources/Private/Language/locallang.xlf:plugin.newsletter',
        'mens_circle_newsletter',
        'tx-menscircle-newsletter'
    ),
);

TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
    new TYPO3\CMS\Core\Schema\Struct\SelectItem(
        'select',
        'LLL:EXT:mens_circle/Resources/Private/Language/locallang.xlf:plugin.testimonials',
        'mens_circle_testimonials',
        'tx-menscircle-testimonials'
    ),
);
