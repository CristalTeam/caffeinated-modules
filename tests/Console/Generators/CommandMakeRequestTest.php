<?php

namespace Caffeinated\Modules\Tests\Commands\Generators;

use PHPUnit\Framework\Attributes\Test;
use Caffeinated\Modules\Tests\BaseTestCase;
use Spatie\Snapshots\MatchesSnapshots;

final class CommandMakeRequestTest extends BaseTestCase
{
    use MatchesSnapshots;

    protected $finder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->finder = $this->app['files'];

        $this->artisan('make:module', ['slug' => 'request', '--quick' => 'quick']);
    }

    #[Test]
    public function it_can_generate_a_new_request_with_default_module_namespace(): void
    {
        $this->artisan('make:module:request', ['slug' => 'request', 'name' => 'DefaultRequest']);

        $file = $this->finder->get(module_path('request').'/Http/Requests/DefaultRequest.php');

        $this->assertMatchesSnapshot($file);
    }

    #[Test]
    public function it_can_generate_a_new_request_with_custom_module_namespace(): void
    {
        $this->app['config']->set("modules.locations.$this->default.namespace", 'App\\CustomRequestNamespace\\');

        $this->artisan('make:module:request', ['slug' => 'request', 'name' => 'CustomRequest']);

        $file = $this->finder->get(module_path('request').'/Http/Requests/CustomRequest.php');

        $this->assertMatchesSnapshot($file);
    }

    protected function tearDown(): void
    {
        $this->finder->deleteDirectory(module_path('request'));

        parent::tearDown();
    }
}