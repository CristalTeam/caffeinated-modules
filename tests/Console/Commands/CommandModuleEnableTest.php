<?php

namespace Caffeinated\Modules\Tests\Commands\Commands;

use PHPUnit\Framework\Attributes\Test;
use Caffeinated\Modules\Tests\BaseTestCase;

final class CommandModuleEnableTest extends BaseTestCase
{
    protected $finder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->finder = $this->app['files'];

        $this->artisan('make:module', ['slug' => 'enable', '--quick' => 'quick']);
    }

    #[Test]
    public function it_can_enable_an_disabled_module(): void
    {
        $this->artisan('module:disable', ['slug' => 'enable']);

        $cached = \Module::where('slug', 'enable');

        $this->assertFalse($cached->toArray()['enabled']);

        $this->artisan('module:enable', ['slug' => 'enable']);

        $cached = \Module::where('slug', 'enable');

        $this->assertTrue($cached->toArray()['enabled']);
    }

    #[Test]
    public function it_can_disable_a_enabled_module(): void
    {
        $this->artisan('module:enable', ['slug' => 'enable']);

        $cached = \Module::where('slug', 'enable');

        $this->assertTrue($cached->toArray()['enabled']);

        $this->artisan('module:disable', ['slug' => 'enable']);

        $cached = \Module::where('slug', 'enable');

        $this->assertFalse($cached->toArray()['enabled']);
    }

    protected function tearDown(): void
    {
        $this->finder->deleteDirectory(module_path('enable'));

        parent::tearDown();
    }
}