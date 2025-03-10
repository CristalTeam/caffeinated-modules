<?php

namespace Caffeinated\Modules\Tests\Commands\Generators;

use PHPUnit\Framework\Attributes\Test;
use Spatie\Snapshots\MatchesSnapshots;
use Caffeinated\Modules\Tests\BaseTestCase;

final class CommandMakeModuleTest extends BaseTestCase
{
    use MatchesSnapshots;

    protected $finder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->finder = $this->app['files'];
    }

    #[Test]
    public function it_can_customize_module_provider(): void
    {
        $this->app['config']->set("modules.locations.{$this->default}.provider", "CustomServiceProvider");

        $this->artisan('make:module', ['slug' => 'custom', '--quick' => 'quick']);

        $this->assertDirectoryExists(module_path('custom', '/Providers'));
        $this->assertFileExists(module_path('custom', '/Providers/CustomServiceProvider.php'));
    }

    #[Test]
    public function it_can_generate_module_with_custom_mapping(): void
    {
        $this->app['config']->set("modules.locations.{$this->default}.mapping", [
            'Config'              => 'config',
            'Database/Factories'  => 'src/Database/Factories',
            'Database/Migrations' => 'src/Database/Migrations',
            'Database/Seeds'      => 'src/Database/Seeds',
            'Http/Controllers'    => 'src/Http/Controllers',
            'Http/Middleware'     => 'src/Http/Middleware',
            'Providers'           => 'src/Providers',
            'Resources/Lang'      => 'resources/lang',
            'Resources/Views'     => 'resources/views',
            'Routes'              => 'routes'
        ]);

        $this->artisan('make:module', ['slug' => 'custom', '--quick' => 'quick']);

        $this->assertDirectoryExists(module_path('custom').'/config');
        $this->assertDirectoryExists(module_path('custom').'/src/Database/Factories');
        $this->assertDirectoryExists(module_path('custom').'/src/Database/Migrations');
        $this->assertDirectoryExists(module_path('custom').'/src/Database/Seeds');
        $this->assertDirectoryExists(module_path('custom').'/src/Http/Controllers');
        $this->assertDirectoryExists(module_path('custom').'/src/Http/Middleware');
        $this->assertDirectoryExists(module_path('custom').'/src/Providers');
        $this->assertDirectoryExists(module_path('custom').'/resources/lang');
        $this->assertDirectoryExists(module_path('custom').'/resources/views');
        $this->assertDirectoryExists(module_path('custom').'/routes');
    }

    protected function tearDown(): void
    {
        $this->finder->deleteDirectory(module_path('custom'));

        parent::tearDown();
    }
}