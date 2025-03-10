<?php

namespace Caffeinated\Modules\Tests\Commands\Generators;

use PHPUnit\Framework\Attributes\Test;
use Caffeinated\Modules\Tests\BaseTestCase;
use Spatie\Snapshots\MatchesSnapshots;

class CommandMakePolicyTest extends BaseTestCase
{
    use MatchesSnapshots;

    protected $finder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->finder = $this->app['files'];

        $this->artisan('make:module', ['slug' => 'policy', '--quick' => 'quick']);
    }

    #[Test]
    public function it_can_generate_a_new_policy_with_default_module_namespace(): void
    {
        $this->artisan('make:module:policy', ['slug' => 'policy', 'name' => 'DefaultPolicy']);

        $file = $this->finder->get(module_path('policy').'/Policies/DefaultPolicy.php');

        $this->assertMatchesSnapshot($file);
    }

    #[Test]
    public function it_can_generate_a_new_policy_with_custom_module_namespace(): void
    {
        $this->app['config']->set("modules.locations.$this->default.namespace", 'App\\CustomPolicyNamespace\\');

        $this->artisan('make:module:policy', ['slug' => 'policy', 'name' => 'CustomPolicy']);

        $file = $this->finder->get(module_path('policy').'/Policies/CustomPolicy.php');

        $this->assertMatchesSnapshot($file);
    }

    protected function tearDown(): void
    {
        $this->finder->deleteDirectory(module_path('policy'));

        parent::tearDown();
    }
}