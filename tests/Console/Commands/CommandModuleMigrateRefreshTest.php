<?php

namespace Caffeinated\Modules\Tests\Commands\Commands;

use PHPUnit\Framework\Attributes\Test;
use Caffeinated\Modules\Tests\BaseTestCase;

class CommandModuleMigrateRefreshTest extends BaseTestCase
{
    protected $finder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->finder = $this->app['files'];

        $this->artisan('make:module', ['slug' => 'migrate-refresh', '--quick' => 'quick']);
    }

    #[Test]
    public function it_can_migrate_refresh_a_module(): void
    {
        $this->assertFalse(\Schema::hasTable('CustomCreateMigrationRefreshTable'));

        $this->artisan('make:module:migration', ['slug' => 'migrate-refresh', 'name' => 'CustomMigrateRefresh', '--create' => 'CustomCreateMigrationRefreshTable']);

        $this->artisan('module:migrate', ['slug' => 'migrate-refresh']);

        $this->assertTrue(\Schema::hasTable('CustomCreateMigrationRefreshTable'));

        $this->artisan('module:migrate:refresh', ['slug' => 'migrate-refresh']);

        $this->assertTrue(\Schema::hasTable('CustomCreateMigrationRefreshTable'));
    }

    protected function tearDown(): void
    {
        $this->finder->deleteDirectory(module_path('migrate-refresh'));

        parent::tearDown();
    }
}