<?php

declare(strict_types=1);

namespace Tests\Architecture;

use PHPat\Selector\Selector;
use PHPat\Test\Builder\Rule;
use PHPat\Test\PHPat;

final class LayerTest
{
    /**
     * Models should not depend on Filament or HTTP classes.
     */
    public function testModelIndependence(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace('App\Models'))
            ->shouldNotDependOn()
            ->classes(Selector::inNamespace('App\Filament'), Selector::inNamespace('App\Http'), Selector::inNamespace('Filament'))
            ->because('Models should be independent of presentation layer');
    }

    /**
     * Services should not depend on Filament or HTTP controllers.
     */
    public function testServiceLayerIndependence(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace('App\Services'))
            ->shouldNotDependOn()
            ->classes(
                Selector::inNamespace('App\Filament'),
                Selector::inNamespace('App\Http\Controllers'),
                Selector::inNamespace('Filament'),
            )
            ->because('Services should not depend on presentation layer');
    }

    /**
     * Enums should not have external dependencies.
     */
    public function testEnumPurity(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace('App\Enums'))
            ->canOnlyDependOn()
            ->classes(Selector::inNamespace('App\Traits'))
            ->because('Enums should be simple value objects');
    }

    /**
     * Actions should be single-purpose and isolated.
     */
    public function testActionIsolation(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace('App\Actions'))
            ->shouldNotDependOn()
            ->classes(Selector::inNamespace('App\Filament'), Selector::inNamespace('App\Http\Controllers'))
            ->because('Actions should be reusable and not tied to presentation');
    }

    /**
     * Mail classes should only depend on models and services.
     */
    public function testMailDependencies(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace('App\Mail'))
            ->canOnlyDependOn()
            ->classes(
                Selector::inNamespace('App\Models'),
                Selector::inNamespace('App\Services'),
                Selector::inNamespace('App\Enums'),
                Selector::inNamespace('Illuminate'),
            )
            ->because('Mail should not depend on controllers or Filament');
    }
}
