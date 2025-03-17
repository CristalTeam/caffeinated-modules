<?php

namespace Caffeinated\Modules\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Caffeinated\Modules\Repositories\Repository;

class ModuleMigrateRefreshCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The command signature.
     *
     * @var string
     */
    protected $signature = 'module:migrate:refresh {slug? : Module slug.}
                            {--database= : The database connection to use.}
                            {--force : Force the operation to run while in production.}
                            {--pretend : Dump the SQL queries that would be run.}
                            {--seed : Indicates if the seed task should be re-run.}
                            {--location= : Which modules location to use.}';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->confirmToProceed()) {
            return;
        }

        $repository = modules()->location($this->option('location'));

        $this->resetMigrations($repository);
    }

    /**
     * Determine if the developer has requested database seeding.
     *
     * @return bool
     */
    protected function needsSeeding()
    {
        return $this->option('seed');
    }

    /**
     * Run the module seeder command.
     *
     * @param string|null $slug
     * @param string|null $database
     */
    protected function runSeeder($slug = null, $database = null)
    {
        $this->call('module:seed', [
            'slug'       => $slug,
            '--database' => $database,
        ]);
    }

    /**
     * Reset and rerun migrations for the module.
     *
     * @param Repository $repository
     */
    protected function resetMigrations(Repository $repository)
    {
        $slug = $this->argument('slug');

        $this->call('module:migrate:reset', [
            'slug' => $slug,
            '--database' => $this->option('database'),
            '--force' => $this->option('force'),
            '--pretend' => $this->option('pretend'),
            '--location' => $repository->location,
        ]);

        $this->call('module:migrate', [
            'slug' => $slug,
            '--database' => $this->option('database'),
            '--location' => $repository->location,
        ]);

        if ($this->needsSeeding()) {
            $this->runSeeder($slug, $this->option('database'));
        }

        if ($slug) {
            $module = $repository->where('slug', $slug);
            event($slug . '.module.refreshed', [$module, $this->option()]);
            $this->info('Module has been refreshed.');
        } else {
            $this->info('All modules have been refreshed.');
        }
    }
}
