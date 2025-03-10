<?php

namespace Caffeinated\Modules\Tests\Commands\Generators;

use PHPUnit\Framework\Attributes\Test;
use Caffeinated\Modules\Tests\BaseTestCase;
use Spatie\Snapshots\MatchesSnapshots;

final class CommandMakeControllerTest extends BaseTestCase
{
    use MatchesSnapshots;

    protected $finder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->finder = $this->app['files'];

        $this->artisan('make:module', ['slug' => 'controller', '--quick' => 'quick']);
    }

    #[Test]
    public function it_can_generate_a_new_controller_with_default_module_namespace(): void
    {
        $this->artisan('make:module:controller', ['slug' => 'controller', 'name' => 'DefaultController']);

        $file = $this->finder->get(module_path('controller').'/Http/Controllers/DefaultController.php');

        $this->assertMatchesSnapshot($file);
    }

    #[Test]
    public function it_can_generate_a_new_controller_resource_with_default_module_namespace(): void
    {
        $this->artisan('make:module:controller', ['slug' => 'controller', 'name' => 'DefaultResourceController', '--resource' => 'resource']);

        $file = $this->finder->get(module_path('controller').'/Http/Controllers/DefaultResourceController.php');

        $this->assertMatchesSnapshot($file);
    }

    #[Test]
    public function it_can_generate_a_new_controller_with_custom_module_namespace(): void
    {
        $this->app['config']->set("modules.locations.$this->default.namespace", 'App\\CustomModuleNamespace\\');

        $this->artisan('make:module:controller', ['slug' => 'controller', 'name' => 'CustomController']);

        $file = $this->finder->get(module_path('controller').'/Http/Controllers/CustomController.php');

        $this->assertMatchesSnapshot($file);
    }

    protected function tearDown(): void
    {
        $this->finder->deleteDirectory(module_path('controller'));

        parent::tearDown();
    }
}