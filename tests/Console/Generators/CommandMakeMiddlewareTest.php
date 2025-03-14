<?php

namespace Caffeinated\Modules\Tests\Commands\Generators;

use PHPUnit\Framework\Attributes\Test;
use Caffeinated\Modules\Tests\BaseTestCase;
use Spatie\Snapshots\MatchesSnapshots;

final class CommandMakeMiddlewareTest extends BaseTestCase
{
    use MatchesSnapshots;

    protected $finder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->finder = $this->app['files'];

        $this->artisan('make:module', ['slug' => 'middleware', '--quick' => 'quick']);
    }

    #[Test]
    public function it_can_generate_a_new_middleware_with_default_module_namespace(): void
    {
        $this->artisan('make:module:middleware', ['slug' => 'middleware', 'name' => 'DefaultMiddleware']);

        $file = $this->finder->get(module_path('middleware').'/Http/Middleware/DefaultMiddleware.php');

        $this->assertMatchesSnapshot($file);
    }

    #[Test]
    public function it_can_generate_a_new_middleware_with_custom_module_namespace(): void
    {
        $this->app['config']->set("modules.locations.$this->default.namespace", 'App\\MiddlewareModules\\');

        $this->artisan('make:module:middleware', ['slug' => 'middleware', 'name' => 'CustomMiddleware']);

        $file = $this->finder->get(module_path('middleware').'/Http/Middleware/CustomMiddleware.php');

        $this->assertMatchesSnapshot($file);
    }

    protected function tearDown(): void
    {
        $this->finder->deleteDirectory(module_path('middleware'));

        parent::tearDown();
    }
}