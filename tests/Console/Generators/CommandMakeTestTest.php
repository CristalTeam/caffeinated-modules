<?php

namespace Caffeinated\Modules\Tests\Commands\Generators;

use PHPUnit\Framework\Attributes\Test;
use Caffeinated\Modules\Tests\BaseTestCase;
use Spatie\Snapshots\MatchesSnapshots;

final class CommandMakeTestTest extends BaseTestCase
{
    use MatchesSnapshots;

    protected $finder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->finder = $this->app['files'];

        $this->artisan('make:module', ['slug' => 'test', '--quick' => 'quick']);
    }

    #[Test]
    public function it_can_generate_a_new_test_with_default_module_namespace(): void
    {
        $this->artisan('make:module:test', ['slug' => 'test', 'name' => 'DefaultTest']);

        $file = $this->finder->get(module_path('test').'/Tests/DefaultTest.php');

        $this->assertMatchesSnapshot($file);
    }

    #[Test]
    public function it_can_generate_a_new_test_with_custom_module_namespace(): void
    {
        $this->app['config']->set("modules.locations.$this->default.namespace", 'App\\CustomTestNamespace\\');

        $this->artisan('make:module:test', ['slug' => 'test', 'name' => 'CustomTest']);

        $file = $this->finder->get(module_path('test').'/Tests/CustomTest.php');

        $this->assertMatchesSnapshot($file);
    }

    protected function tearDown(): void
    {
        $this->finder->deleteDirectory(module_path('test'));

        parent::tearDown();
    }
}