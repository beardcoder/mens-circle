<?php

declare(strict_types=1);

namespace Tests\Architecture;

use PHPat\Selector\Selector;
use PHPat\Test\Builder\Rule;
use PHPat\Test\PHPat;

final class CodeQualityTest
{
    /**
     * Settings classes should be final.
     */
    public function testSettingsAreFinal(): Rule
    {
        return PHPat::rule()->classes(Selector::inNamespace('App\Settings'))->should()->beFinal();
    }

    /**
     * Models should not depend on Filament.
     */
    public function testModelsAreIndependent(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace('App\Models'))
            ->shouldNot()->dependOn()
            ->classes(Selector::inNamespace('App\Filament'))
            ->because('Models should be independent of UI layer');
    }
}
