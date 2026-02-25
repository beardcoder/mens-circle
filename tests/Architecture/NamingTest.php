<?php

declare(strict_types=1);

namespace Tests\Architecture;

use PHPat\Selector\Selector;
use PHPat\Test\Builder\Rule;
use PHPat\Test\PHPat;

final class NamingTest
{
    /**
     * Services should not depend on controllers or Filament.
     */
    public function testServiceLayerClean(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace('App\Services'))
            ->shouldNotDependOn()
            ->classes(Selector::inNamespace('App\Http\Controllers'), Selector::inNamespace('App\Filament'))
            ->because('Services should be framework-agnostic');
    }
}
