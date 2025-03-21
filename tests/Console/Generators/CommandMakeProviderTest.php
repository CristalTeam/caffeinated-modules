<?php

namespace Caffeinated\Modules\Tests\Commands\Generators;

use PHPUnit\Framework\Attributes\Test;
use Caffeinated\Modules\Tests\BaseTestCase;
use Spatie\Snapshots\MatchesSnapshots;

final class CommandMakeProviderTest extends BaseTestCase
{
    use MatchesSnapshots;

    protected $finder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->finder = $this->app['files'];

        $this->artisan('make:module', ['slug' => 'provider', '--quick' => 'quick']);
    }

    #[Test]
    public function it_can_generate_a_new_provider_with_default_module_namespace(): void
    {
        $this->artisan('make:module:provider', ['slug' => 'provider', 'name' => 'DefaultProvider']);

        $file = $this->finder->get(module_path('provider').'/Providers/DefaultProvider.php');

        $this->assertMatchesSnapshot($file);
    }

    #[Test]
    public function it_can_generate_a_new_provider_with_custom_module_namespace(): void
    {
        $this->app['config']->set("modules.locations.$this->default.namespace", 'App\\CustomProviderNamespace\\');

        $this->artisan('make:module:provider', ['slug' => 'provider', 'name' => 'CustomProvider']);

        $file = $this->finder->get(module_path('provider').'/Providers/CustomProvider.php');

        $this->assertMatchesSnapshot($file);
    }

    protected function tearDown(): void
    {
        $this->finder->deleteDirectory(module_path('provider'));

        parent::tearDown();
    }
}