<?php

namespace Caffeinated\Modules\Tests\Commands\Commands;

use PHPUnit\Framework\Attributes\Test;
use Caffeinated\Modules\Tests\BaseTestCase;

final class CommandModuleDisableTest extends BaseTestCase
{
    protected $finder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->finder = $this->app['files'];

        $this->artisan('make:module', ['slug' => 'disable', '--quick' => 'quick']);
    }

    #[Test]
    public function it_can_disable_an_enabled_module(): void
    {
        $cached = \Module::where('slug', 'disable');

        $this->assertTrue($cached->toArray()['enabled']);

        $this->artisan('module:disable', ['slug' => 'disable']);

        $cached = \Module::where('slug', 'disable');

        $this->assertFalse($cached->toArray()['enabled']);
    }

    #[Test]
    public function it_can_enable_a_disabled_module(): void
    {
        $this->artisan('module:disable', ['slug' => 'disable']);

        $cached = \Module::where('slug', 'disable');

        $this->assertFalse($cached->toArray()['enabled']);

        $this->artisan('module:enable', ['slug' => 'disable']);

        $cached = \Module::where('slug', 'disable');

        $this->assertTrue($cached->toArray()['enabled']);
    }

    protected function tearDown(): void
    {
        $this->finder->deleteDirectory(module_path('disable'));

        parent::tearDown();
    }
}