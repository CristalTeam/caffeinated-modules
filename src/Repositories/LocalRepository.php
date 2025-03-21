<?php

namespace Caffeinated\Modules\Repositories;

use Illuminate\Support\Str;

class LocalRepository extends Repository
{
    /**
     * Get all modules.
     *
     * @return \Illuminate\Support\Collection
     */
    public function all()
    {
        return $this->getCache()->sortBy('order');
    }

    /**
     * Get all module slugs.
     *
     * @return \Illuminate\Support\Collection
     */
    public function slugs()
    {
        $slugs = collect();

        $this->all()->each(function ($item, $key) use ($slugs): void {
            $slugs->push(strtolower((string) $item['slug']));
        });

        return $slugs;
    }

    /**
     * Get modules based on where clause.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return \Illuminate\Support\Collection
     */
    public function where($key, $value)
    {
        return collect($this->all()->where($key, $value)->first());
    }

    /**
     * Sort modules by given key in ascending order.
     *
     * @param string $key
     *
     * @return \Illuminate\Support\Collection
     */
    public function sortBy($key)
    {
        $collection = $this->all();

        return $collection->sortBy($key);
    }

    /**
     * Sort modules by given key in ascending order.
     *
     * @param string $key
     *
     * @return \Illuminate\Support\Collection
     */
    public function sortByDesc($key)
    {
        $collection = $this->all();

        return $collection->sortByDesc($key);
    }

    /**
     * Determines if the given module exists.
     *
     * @param string $slug
     *
     * @return bool
     */
    public function exists($slug)
    {
        return ($this->slugs()->contains($slug) || $this->slugs()->contains(Str::slug($slug)));
    }

    /**
     * Returns count of all modules.
     *
     * @return int
     */
    public function count()
    {
        return $this->all()->count();
    }

    /**
     * Get a module property value.
     *
     * @param string $property
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($property, $default = null)
    {
        [$slug, $key] = explode('::', $property);

        $module = $this->where('slug', $slug);

        return $module->get($key, $default);
    }

    /**
     * Set the given module property value.
     *
     * @param string $property
     * @param mixed  $value
     *
     * @return bool
     */
    public function set($property, $value)
    {
        [$slug, $key] = explode('::', $property);

        $cachePath = $this->getCachePath();
        $cache = $this->getCache();
        $module = $this->where('slug', $slug);

        if (isset($module[$key])) {
            unset($module[$key]);
        }

        $module[$key] = $value;

        $module = collect([$module['basename'] => $module]);

        $merged = $cache->merge($module);
        $content = json_encode($merged->all(), JSON_PRETTY_PRINT);

        return $this->files->put($cachePath, $content);
    }

    /**
     * Get all enabled modules.
     *
     * @return \Illuminate\Support\Collection
     */
    public function enabled()
    {
        return $this->all()->where('enabled', true);
    }

    /**
     * Get all disabled modules.
     *
     * @return \Illuminate\Support\Collection
     */
    public function disabled()
    {
        return $this->all()->where('enabled', false);
    }

    /**
     * Check if specified module is enabled.
     *
     * @param string $slug
     *
     * @return bool
     */
    public function isEnabled($slug)
    {
        $module = $this->where('slug', $slug);

        return $module['enabled'] === true;
    }

    /**
     * Check if specified module is disabled.
     *
     * @param string $slug
     *
     * @return bool
     */
    public function isDisabled($slug)
    {
        $module = $this->where('slug', $slug);

        return $module['enabled'] === false;
    }

    /**
     * Enables the specified module.
     *
     * @param string $slug
     *
     * @return bool
     */
    public function enable($slug)
    {
        return $this->set($slug.'::enabled', true);
    }

    /**
     * Disables the specified module.
     *
     * @param string $slug
     *
     * @return bool
     */
    public function disable($slug)
    {
        return $this->set($slug.'::enabled', false);
    }
    
    /**
     * Get all modules by specified location
     * 
     * @param string $location
     * 
     * @return \Illuminate\Support\Collection
     */
    public function byLocation($location)
    {
        $manifest = $this->getCachePath($location); 

        return collect(json_decode($this->files->get($manifest), true));
    }

    /*
    |--------------------------------------------------------------------------
    | Optimization Methods
    |--------------------------------------------------------------------------
    |
    */

    /**
     * Update cached repository of module information.
     *
     * @return bool
     */
    public function optimize()
    {
        $cachePath = $this->getCachePath();

        $cache     = $this->getCache();
        $basenames = $this->getAllBasenames();
        $modules   = collect();

        $basenames->each(function ($module, $key) use ($modules, $cache): void {
            $basename = collect(['basename' => $module]);
            $temp     = $basename->merge(collect($cache->get($module)));
            $manifest = $temp->merge(collect($this->getManifest($module)));

            $modules->put($module, $manifest);
        });

        $modules->each(function ($module) {
            $module->put('id', crc32((string) $module->get('slug')));

            if (!$module->has('enabled')) {
                $module->put('enabled', config("modules.locations.$this->location.enabled", true));
            }

            if (!$module->has('order')) {
                $module->put('order', 9001);
            }

            return $module;
        });

        $content = json_encode($modules->all(), JSON_PRETTY_PRINT);

        return $this->files->put($cachePath, $content);
    }

    /**
     * Get the contents of the cache file.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getCache()
    {
        $cachePath = $this->getCachePath();

        if (!$this->files->exists($cachePath)) {
            $this->createCache();

            $this->optimize();
        }

        return collect(json_decode($this->files->get($cachePath), true));
    }

    /**
     * Create an empty instance of the cache file.
     *
     * @return \Illuminate\Support\Collection
     */
    private function createCache()
    {
        $cachePath = $this->getCachePath();
        $content   = json_encode([], JSON_PRETTY_PRINT);

        $this->files->put($cachePath, $content);

        return collect(json_decode($content, true));
    }

    /**
     * Get the path to the cache file.
     *
     * @return string
     */
    private function getCachePath($location = null)
    {
        if (!$this->files->isDirectory(storage_path("app/modules"))) {
            $this->files->makeDirectory(storage_path("app/modules"));
        }

        $location = Str::slug($location ?? $this->location);

        return storage_path("app/modules/$location.json");
    }
}
