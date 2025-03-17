<?php

namespace Caffeinated\Modules\Console\Commands;

use Illuminate\Console\Command;

class ModuleEnableCommand extends Command
{
    /**
     * The command signature.
     *
     * @var string
     */
    protected $signature = 'module:enable {slug : Module slug.} {--location= : Which modules location to use.}';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $slug = $this->argument('slug');
        $repository = modules($this->option('location'));

        if ($repository->isDisabled($slug)) {
            $repository->enable($slug);
            
            $module = $repository->where('slug', $slug);
            event($slug.'.module.enabled', [$module, null]);
            
            $this->info('Module was enabled successfully.');
        } else {
            $this->comment('Module is already enabled.');
        }
    }
}
