<?php
$file = '/Users/markus.sommer/Projekte/Privat/mens-circle/packages/mens_circle/Configuration/TCA/Overrides/20_tt_content_elements.php';
$content = file_get_contents($file);

// Define the replacements for each content element
$replacements = [
    'menscircle_faq' => 'Faq',
    'menscircle_intro' => 'Intro',
    'menscircle_journey' => 'Journey',
    'menscircle_moderator' => 'Moderator',
    'menscircle_values' => 'Values',
    'menscircle_whatsapp' => 'WhatsApp',
];

foreach ($replacements as $ctype => $flexform) {
    $old = <<<EOD
            'pi_flexform' => [
                'config' => [
                    'ds' => [
                        'default' => 'FILE:EXT:mens_circle/Configuration/FlexForms/{$flexform}.xml',
                    ],
                ],
            ],
EOD;

    $new = <<<EOD
            'pi_flexform' => [
                'config' => [
                    'type' => 'flex',
                    'ds_pointerField' => 'CType',
                    'ds' => [
                        'default' => 'FILE:EXT:mens_circle/Configuration/FlexForms/{$flexform}.xml',
                        '{$ctype}' => 'FILE:EXT:mens_circle/Configuration/FlexForms/{$flexform}.xml',
                    ],
                ],
            ],
EOD;

    $content = str_replace($old, $new, $content);
}

file_put_contents($file, $content);
echo "FlexForms fixed!\n";
