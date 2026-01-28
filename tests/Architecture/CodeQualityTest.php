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
        return PHPat::rule()
            ->classes(Selector::inNamespace('App\Settings'))
            ->shouldBeFinal();
    }

    /**
     * Models should not depend on Filament.
     */
    public function testModelsAreIndependent(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace('App\Models'))
            ->shouldNotDependOn()
            ->classes(Selector::inNamespace('App\Filament'))
            ->because('Models should be independent of UI layer');
    }
}
