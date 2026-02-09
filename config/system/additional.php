<?php

declare(strict_types=1);

if (!defined('TYPO3')) {
    die('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['SYS']['trustedHostsPattern'] = '.*';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'] = 'Männerkreis Niederbayern/ Straubing';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'] = '';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['displayErrors'] = 0;
$GLOBALS['TYPO3_CONF_VARS']['BE']['debug'] = false;
