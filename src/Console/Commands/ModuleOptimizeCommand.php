<?php

namespace Caffeinated\Modules\Console\Commands;

use Illuminate\Console\Command;

class ModuleOptimizeCommand extends Command
{
    /**
     * The command signature.
     *
     * @var string
     */
    protected $signature = 'module:optimize {--location= : Which modules location to use.}';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($location = $this->option('location')) {
            $this->info('Generating optimized module cache...');

            $repository = modules($location);
            $repository->optimize();

            event('modules.optimized', [$repository->all()]);
        } else {
            foreach (modules()->repositories() as $repository) {
                $this->info("Generating optimized module cache for [$repository->location]...");

                $repository->optimize();

                event('modules.optimized', [$repository->all()]);
            }
        }
    }
}
