<?php

namespace Caffeinated\Modules\Tests\Commands\Generators;

use PHPUnit\Framework\Attributes\Test;
use Caffeinated\Modules\Tests\BaseTestCase;
use Spatie\Snapshots\MatchesSnapshots;

final class CommandMakeSeederTest extends BaseTestCase
{
    use MatchesSnapshots;

    protected $finder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->finder = $this->app['files'];

        $this->artisan('make:module', ['slug' => 'seeder', '--quick' => 'quick']);
    }

    #[Test]
    public function it_can_generate_a_new_seeder_with_default_module_namespace(): void
    {
        $this->artisan('make:module:seeder', ['slug' => 'seeder', 'name' => 'DefaultSeeder']);

        $file = $this->finder->get(module_path('seeder').'/Database/Seeds/DefaultSeeder.php');

        $this->assertMatchesSnapshot($file);
    }

    #[Test]
    public function it_can_generate_a_new_seeder_with_custom_module_namespace(): void
    {
        $this->app['config']->set("modules.locations.$this->default.namespace", 'App\\CustomSeederNamespace\\');

        $this->artisan('make:module:seeder', ['slug' => 'seeder', 'name' => 'CustomSeeder']);

        $file = $this->finder->get(module_path('seeder').'/Database/Seeds/CustomSeeder.php');

        $this->assertMatchesSnapshot($file);
    }

    protected function tearDown(): void
    {
        $this->finder->deleteDirectory(module_path('seeder'));

        parent::tearDown();
    }
}