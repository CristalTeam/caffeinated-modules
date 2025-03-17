<?php

namespace Caffeinated\Modules\Console\Commands;

use Illuminate\Console\Command;

class ModuleDisableCommand extends Command
{
    /**
     * The command signature.
     *
     * @var string
     */
    protected $signature = 'module:disable {slug : Module slug.} {--location= : Which modules location to use.}';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $slug = $this->argument('slug');
        $repository = modules($this->option('location'));

        if ($repository->isEnabled($slug)) {
            $repository->disable($slug);
            
            $module = $repository->where('slug', $slug);
            event($slug.'.module.disabled', [$module, null]);
            
            $this->info('Module was disabled successfully.');
        } else {
            $this->comment('Module is already disabled.');
        }
    }
}
