<?php

namespace Caffeinated\Modules\Console\Commands;

use Illuminate\Console\Command;
use Caffeinated\Modules\RepositoryManager;
use Caffeinated\Modules\Repositories\Repository;

class ModuleSeedCommand extends Command
{
    /**
     * The command signature.
     *
     * @var string
     */
    protected $signature = 'module:seed 
        {slug? : Module slug.} 
        {--class= : The class name of the module\'s root seeder.} 
        {--database= : The database connection to seed.} 
        {--force : Force the operation to run while in production.} 
        {--location= : Which modules location to use.}';

    /**
     * @var RepositoryManager
     */
    protected RepositoryManager $module;

    /**
     * Create a new command instance.
     */
    public function __construct(RepositoryManager $module)
    {
        parent::__construct();
        $this->module = $module;
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $repository = modules($this->option('location') ?? config('modules.default_location'));
        $slug = $this->argument('slug');

        if ($slug) {
            if (!$repository->exists($slug)) {
                $this->error('Module does not exist.');
                return;
            }

            if ($repository->isEnabled($slug) || $this->option('force')) {
                $this->seed($slug, $repository);
            }

            return;
        }

        $modules = $this->option('force') ? $repository->all() : $repository->enabled();

        foreach ($modules as $module) {
            $this->seed($module['slug'], $repository);
        }
    }

    /**
     * Seed the specific module.
     */
    protected function seed(string $slug, Repository $repository): void
    {
        $module = $repository->where('slug', $slug);
        $namespacePath = $repository->getNamespace();
        $rootSeeder = $module['basename'] . 'DatabaseSeeder';
        $fullPath = "{$namespacePath}\\{$module['basename']}\\Database\\Seeds\\{$rootSeeder}";

        if (class_exists($fullPath)) {
            $params = [
                '--class' => $this->option('class') ?? $fullPath,
                '--database' => $this->option('database'),
                '--force' => $this->option('force'),
            ];

            $this->call('db:seed', array_filter($params));
            event("{$slug}.module.seeded", [$module, $this->options()]);
        }
    }
}
