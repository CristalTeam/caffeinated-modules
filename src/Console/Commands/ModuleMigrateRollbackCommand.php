<?php

namespace Caffeinated\Modules\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Caffeinated\Modules\RepositoryManager;
use Illuminate\Database\Migrations\Migrator;
use Caffeinated\Modules\Traits\MigrationTrait;
use Caffeinated\Modules\Repositories\Repository;

class ModuleMigrateRollbackCommand extends Command
{
    use MigrationTrait, ConfirmableTrait;

    /**
     * The command signature.
     *
     * @var string
     */
    protected $signature = 'module:migrate:rollback {slug? : Module slug.}
                            {--database= : The database connection to use.}
                            {--force : Force the operation to run while in production.}
                            {--pretend : Dump the SQL queries that would be run.}
                            {--step= : The number of migrations to be reverted.}
                            {--location= : Which modules location to use.}';

    /**
     * The migrator instance.
     *
     * @var Migrator
     */
    protected $migrator;

    /**
     * @var RepositoryManager
     */
    protected $module;

    /**
     * Create a new command instance.
     */
    public function __construct(Migrator $migrator, RepositoryManager $module)
    {
        parent::__construct();
        $this->migrator = $migrator;
        $this->module   = $module;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->confirmToProceed()) {
            return;
        }

        $this->migrator->setConnection($this->option('database'));
        
        $repository = modules()->location($this->option('location'));
        $paths      = $this->getMigrationPaths($repository);

        $this->migrator->setOutput($this->output)->rollback(
            $paths, ['pretend' => $this->option('pretend'), 'step' => (int) $this->option('step')]
        );
    }

    /**
     * Get all of the migration paths.
     */
    protected function getMigrationPaths(Repository $repository): array
    {
        $slug  = $this->argument('slug');
        $paths = [];

        if ($slug) {
            $paths[] = module_path($slug, 'Database/Migrations', $repository->location);
        } else {
            foreach ($repository->all() as $module) {
                $paths[] = module_path($module['slug'], 'Database/Migrations', $repository->location);
            }
        }

        return $paths;
    }
}
