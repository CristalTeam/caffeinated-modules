<?php

namespace Caffeinated\Modules\Tests\Commands\Commands;

use PHPUnit\Framework\Attributes\Test;
use Caffeinated\Modules\Tests\BaseTestCase;

final class CommandModuleMigrateTest extends BaseTestCase
{
    protected $finder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->finder = $this->app['files'];

        $this->artisan('make:module', ['slug' => 'migrate', '--quick' => 'quick']);
    }

    #[Test]
    public function it_can_migrate_a_module(): void
    {
        $this->assertFalse(\Schema::hasTable('CustomCreateMigrationTable'));

        $this->artisan('make:module:migration', ['slug' => 'migrate', 'name' => 'CustomMigrate', '--create' => 'CustomCreateMigrationTable']);

        $this->artisan('module:migrate', ['slug' => 'migrate']);

        $this->assertTrue(\Schema::hasTable('CustomCreateMigrationTable'));
    }

    protected function tearDown(): void
    {
        $this->finder->deleteDirectory(module_path('migrate'));

        parent::tearDown();
    }
}