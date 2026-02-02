<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;

$iconsPath = GeneralUtility::getFileAbsFileName('EXT:mens_circle/Resources/Public/Icons');

if (!is_dir($iconsPath)) {
    return [];
}

$svgFiles = glob("$iconsPath/*.svg") ?: [];

return array_combine(
    array_map(
        static fn(string $file): string => 'tx-menscircle-' . strtolower(pathinfo($file, PATHINFO_FILENAME)),
        $svgFiles
    ),
    array_map(
        static fn(string $file): array => [
            'provider' => SvgIconProvider::class,
            'source' => 'EXT:mens_circle/Resources/Public/Icons/' . basename($file),
        ],
        $svgFiles
    )
);
