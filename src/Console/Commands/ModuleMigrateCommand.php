<?php

namespace Caffeinated\Modules\Console\Commands;

use Illuminate\Support\Arr;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Caffeinated\Modules\RepositoryManager;
use Illuminate\Database\Migrations\Migrator;
use Caffeinated\Modules\Repositories\Repository;

class ModuleMigrateCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The command signature.
     *
     * @var string
     */
    protected $signature = 'module:migrate {slug? : Module slug.}
                            {--database= : The database connection to use.}
                            {--force : Force the operation to run while in production.}
                            {--pretend : Dump the SQL queries that would be run.}
                            {--seed : Indicates if the seed task should be re-run.}
                            {--step : Force the migrations to be run so they can be rolled back individually.}
                            {--location= : Which modules location to use.}';

    /**
     * @var RepositoryManager
     */
    protected $module;

    /**
     * @var Migrator
     */
    protected $migrator;

    /**
     * Create a new command instance.
     */
    public function __construct(Migrator $migrator, RepositoryManager $module)
    {
        parent::__construct();
        $this->migrator = $migrator;
        $this->module = $module;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->prepareDatabase();
        $repository = modules()->location($this->option('location'));
        $this->migrate($repository);
    }

    protected function migrate(Repository $repository)
    {
        if ($slug = $this->argument('slug')) {
            $module = $repository->where('slug', $slug);

            if ($repository->isEnabled($slug) || $this->option('force')) {
                return $this->executeMigrations($slug, $repository->location);
            }

            $this->error('Nothing to migrate.');
        } else {
            $modules = $this->option('force') ? $repository->all() : $repository->enabled();
            foreach ($modules as $module) {
                $this->executeMigrations($module['slug'], $repository->location);
            }
        }
    }

    protected function executeMigrations($slug, $location)
    {
        if (!modules($location)->exists($slug)) {
            return $this->error('Module does not exist.');
        }

        $module = modules($location)->where('slug', $slug);
        $pretend = $this->option('pretend') ?? false;
        $step = $this->option('step') ?? false;
        $path = $this->getMigrationPath($slug);

        $this->migrator->setOutput($this->output)->run($path, ['pretend' => $pretend, 'step' => $step]);
        event("{$slug}.module.migrated", [$module, $this->option()]);

        if ($this->option('seed')) {
            $this->call('module:seed', ['module' => $slug, '--force' => true]);
        }
    }

    protected function getMigrationPath($slug)
    {
        return module_path($slug, 'Database/Migrations', $this->option('location'));
    }

    protected function prepareDatabase()
    {
        $this->migrator->setConnection($this->option('database'));
        
        if (!$this->migrator->repositoryExists()) {
            $this->call('migrate:install', ['--database' => $this->option('database')]);
        }
    }
}
