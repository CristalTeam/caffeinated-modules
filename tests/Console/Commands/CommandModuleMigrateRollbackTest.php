<?php

namespace Caffeinated\Modules\Tests\Commands\Commands;

use PHPUnit\Framework\Attributes\Test;
use Caffeinated\Modules\Tests\BaseTestCase;

final class CommandModuleMigrateRollbackTest extends BaseTestCase
{
    protected $finder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->finder = $this->app['files'];

        $this->artisan('make:module', ['slug' => 'migrate-rollback', '--quick' => 'quick']);
    }

    #[Test]
    public function it_can_migrate_rollback_a_module(): void
    {
        $this->assertFalse(\Schema::hasTable('CustomCreateMigrationRollbackTable'));

        $this->artisan('make:module:migration', ['slug' => 'migrate-rollback', 'name' => 'CustomMigrateRollback', '--create' => 'CustomCreateMigrationRollbackTable']);

        $this->artisan('module:migrate', ['slug' => 'migrate-rollback']);

        $this->assertTrue(\Schema::hasTable('CustomCreateMigrationRollbackTable'));

        $this->artisan('module:migrate:rollback', ['slug' => 'migrate-rollback']);

        $this->assertFalse(\Schema::hasTable('CustomCreateMigrationRollbackTable'));
    }

    protected function tearDown(): void
    {
        $this->finder->deleteDirectory(module_path('migrate-rollback'));

        parent::tearDown();
    }
}