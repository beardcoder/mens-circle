<?php

declare(strict_types=1);

namespace BeardCoder\FluidForms\ViewHelpers;

use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * ViewHelper to include FluidForms JavaScript and CSS assets.
 *
 * Usage in your layout or template:
 *   <ff:assets />
 *
 * This registers the FluidForms JS and CSS via TYPO3's AssetCollector.
 */
class AssetsViewHelper extends AbstractViewHelper
{
    public function render(): string
    {
        $assetCollector = GeneralUtility::makeInstance(AssetCollector::class);

        $jsPath = 'EXT:fluid_forms/Resources/Public/JavaScript/FluidForms.js';
        $cssPath = 'EXT:fluid_forms/Resources/Public/Css/FluidForms.css';

        $assetCollector->addJavaScript(
            'fluidforms-js',
            PathUtility::getPublicResourceWebPath($jsPath),
            ['defer' => 'defer'],
        );

        $assetCollector->addStyleSheet(
            'fluidforms-css',
            PathUtility::getPublicResourceWebPath($cssPath),
        );

        return '';
    }
}
