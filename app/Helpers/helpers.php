<?php

declare(strict_types=1);

use App\Settings\GeneralSettings;

if (! function_exists('app_settings')) {
    function app_settings(): GeneralSettings
    {
        return resolve(GeneralSettings::class);
    }
}
