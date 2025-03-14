<?php

namespace Caffeinated\Modules\Tests;

use PHPUnit\Framework\Attributes\Test;

final class BladeTest extends BaseTestCase
{
    protected $finder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->finder = $this->app['files'];

        $this->artisan('make:module', ['slug' => 'blade', '--quick' => 'quick']);
    }

    #[Test]
    public function it_has_module_if_module_exists_and_is_enabled(): void
    {
        $this->artisan('module:enable', ['slug' => 'blade']);

        $this->assertEquals('has module', $this->renderView('module', ['module' => 'blade']));
    }

    #[Test]
    public function it_has_no_module_if_module_dont_exists(): void
    {
        $this->assertEquals('no module', $this->renderView('module', ['module' => 'dontexists']));
    }

    #[Test]
    public function it_has_no_module_if_module_exists_but_is_not_enabled(): void
    {
        $this->artisan('module:disable', ['slug' => 'blade']);

        $this->assertEquals('no module', $this->renderView('module', ['module' => 'blade']));
    }

    protected function renderView($view, $parameters)
    {
        $this->artisan('view:clear');

        return trim((string)(view($view)->with($parameters)));
    }

    protected function tearDown(): void
    {
        $this->finder->deleteDirectory(module_path('blade'));

        parent::tearDown();
    }
}
