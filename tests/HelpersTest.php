<?php

namespace Caffeinated\Modules\Tests;

use PHPUnit\Framework\Attributes\Test;

final class HelpersTest extends BaseTestCase
{
    protected $finder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->finder = $this->app['files'];

        $this->artisan('make:module', ['slug' => 'helper', '--quick' => 'quick']);
    }

    #[Test]
    public function it_can_get_module_path(): void
    {
        $this->assertSame(base_path().'/modules/Helper', module_path('helper'));
    }

    #[Test]
    public function it_can_get_module_path_files(): void
    {
        $this->assertSame(base_path().'/modules/Helper/Database', module_path('helper', 'Database'));
        $this->assertSame(base_path().'/modules/Helper/Database/Factories', module_path('helper', 'Database/Factories'));
        $this->assertSame(base_path().'/modules/Helper/Database/Migrations', module_path('helper', 'Database/Migrations'));
        $this->assertSame(base_path().'/modules/Helper/Database/Seeds', module_path('helper', 'Database/Seeds'));

        $this->assertSame(base_path().'/modules/Helper/Providers/ModuleServiceProvider.php', module_path('helper', 'Providers/ModuleServiceProvider.php'));
        $this->assertSame(base_path().'/modules/Helper/Providers/RouteServiceProvider.php', module_path('helper', 'Providers/RouteServiceProvider.php'));

        $this->assertSame(base_path().'/modules/Helper/Routes/api.php', module_path('helper', 'Routes/api.php'));
        $this->assertSame(base_path().'/modules/Helper/Routes/web.php', module_path('helper', 'Routes/web.php'));
    }

    #[Test]
    public function it_can_get_module_class(): void
    {
        $this->assertSame('App\Modules\Helper\Database\Factories', module_class('helper', 'Database\\Factories'));
        $this->assertSame('App\Modules\Helper\Database\Migrations', module_class('helper', 'Database\\Migrations'));
        $this->assertSame('App\Modules\Helper\Database\Seeds', module_class('helper', 'Database\\Seeds'));

        $this->assertSame('App\Modules\Helper\Http\Controllers', module_class('helper', 'Http\\Controllers'));

        $this->assertSame('App\Modules\Helper\Http\Middleware', module_class('helper', 'Http\Middleware'));
        $this->assertSame('App\Modules\Helper\Http\Middleware', module_class('helper', 'Http\\Middleware'));

        $this->assertSame('App\Modules\Helper\Providers', module_class('helper', 'Providers'));
    }

    protected function tearDown(): void
    {
        $this->finder->deleteDirectory(module_path('helper'));

        parent::tearDown();
    }
}