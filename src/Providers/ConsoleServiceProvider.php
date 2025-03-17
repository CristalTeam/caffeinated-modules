<?php

namespace Caffeinated\Modules\Providers;

use Caffeinated\Modules\Database\Migrations\Migrator;
use Illuminate\Support\ServiceProvider;

class ConsoleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the provided services.
     */
    public function boot()
    {
        //
    }

    /**
     * Register the provided services.
     */
    public function register()
    {
        $this->registerDisableCommand();
        $this->registerEnableCommand();
        $this->registerListCommand();
        $this->registerMigrateCommand();
        $this->registerMigrateRefreshCommand();
        $this->registerMigrateResetCommand();
        $this->registerMigrateRollbackCommand();
        $this->registerOptimizeCommand();
        $this->registerSeedCommand();
    }

    /**
     * Register the module:disable command.
     */
    protected function registerDisableCommand()
    {
        $this->app->singleton('command.module.disable', fn() => new \Caffeinated\Modules\Console\Commands\ModuleDisableCommand());

        $this->commands('command.module.disable');
    }

    /**
     * Register the module:enable command.
     */
    protected function registerEnableCommand()
    {
        $this->app->singleton('command.module.enable', fn() => new \Caffeinated\Modules\Console\Commands\ModuleEnableCommand());

        $this->commands('command.module.enable');
    }

    /**
     * Register the module:list command.
     */
    protected function registerListCommand()
    {
        $this->app->singleton('command.module.list', fn($app) => new \Caffeinated\Modules\Console\Commands\ModuleListCommand($app['modules']));

        $this->commands('command.module.list');
    }

    /**
     * Register the module:migrate command.
     */
    protected function registerMigrateCommand()
    {
        $this->app->singleton('command.module.migrate', fn($app) => new \Caffeinated\Modules\Console\Commands\ModuleMigrateCommand($app['migrator'], $app['modules']));

        $this->commands('command.module.migrate');
    }

    /**
     * Register the module:migrate:refresh command.
     */
    protected function registerMigrateRefreshCommand()
    {
        $this->app->singleton('command.module.migrate.refresh', fn() => new \Caffeinated\Modules\Console\Commands\ModuleMigrateRefreshCommand());

        $this->commands('command.module.migrate.refresh');
    }

    /**
     * Register the module:migrate:reset command.
     */
    protected function registerMigrateResetCommand()
    {
        $this->app->singleton('command.module.migrate.reset', fn($app) => new \Caffeinated\Modules\Console\Commands\ModuleMigrateResetCommand($app['modules'], $app['files'], $app['migrator']));

        $this->commands('command.module.migrate.reset');
    }

    /**
     * Register the module:migrate:rollback command.
     */
    protected function registerMigrateRollbackCommand()
    {
        $this->app->singleton('command.module.migrate.rollback', function ($app) {
            $repository = $app['migration.repository'];
            $table = $app['config']['database.migrations'];

            $migrator = new Migrator($table, $repository, $app['db'], $app['files']);

            return new \Caffeinated\Modules\Console\Commands\ModuleMigrateRollbackCommand($migrator, $app['modules']);
        });

        $this->commands('command.module.migrate.rollback');
    }

    /**
     * Register the module:optimize command.
     */
    protected function registerOptimizeCommand()
    {
        $this->app->singleton('command.module.optimize', fn() => new \Caffeinated\Modules\Console\Commands\ModuleOptimizeCommand());

        $this->commands('command.module.optimize');
    }

    /**
     * Register the module:seed command.
     */
    protected function registerSeedCommand()
    {
        $this->app->singleton('command.module.seed', fn($app) => new \Caffeinated\Modules\Console\Commands\ModuleSeedCommand($app['modules']));

        $this->commands('command.module.seed');
    }
}
