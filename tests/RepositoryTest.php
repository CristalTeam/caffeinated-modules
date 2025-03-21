<?php

namespace Caffeinated\Modules\Tests;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Collection;

final class RepositoryTest extends BaseTestCase
{
    protected $finder;

    /**
     * @var \Caffeinated\Modules\Repositories\Repository
     */
    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->finder = $this->app['files'];

        $this->repository = modules();

        $this->artisan('make:module', ['slug' => 'RepositoryMod2', '--quick' => 'quick']);

        $this->artisan('make:module', ['slug' => 'RepositoryMod1', '--quick' => 'quick']);

        $this->artisan('make:module', ['slug' => 'RepositoryMod3', '--quick' => 'quick']);
    }

    #[Test]
    public function it_can_check_if_module_is_disabled(): void
    {
        $this->assertFalse($this->repository->isDisabled('repositorymod1'));

        $this->repository->disable('repositorymod1');

        $this->assertTrue($this->repository->isDisabled('repositorymod1'));
    }

    #[Test]
    public function it_can_check_if_module_is_enabled(): void
    {
        $this->assertTrue($this->repository->isEnabled('repositorymod1'));

        $this->repository->disable('repositorymod1');

        $this->assertFalse($this->repository->isEnabled('repositorymod1'));
    }

    #[Test]
    public function it_can_check_if_the_module_exists(): void
    {
        $this->assertTrue($this->repository->exists('repositorymod1'));

        $this->assertFalse($this->repository->exists('repositorymod4'));
    }

    #[Test]
    public function it_can_count_the_modules(): void
    {
        $this->assertSame(3, (int)$this->repository->count());
    }

    #[Test]
    public function it_can_get_a_collection_of_disabled_modules(): void
    {
        $this->assertSame(0, (int)$this->repository->disabled()->count());

        $this->repository->disable('repositorymod1');

        $this->assertSame(1, (int)$this->repository->disabled()->count());
    }

    #[Test]
    public function it_can_get_a_collection_of_enabled_modules(): void
    {
        $this->assertSame(3, (int)$this->repository->enabled()->count());

        $this->repository->disable('repositorymod1');

        $this->assertSame(2, (int)$this->repository->enabled()->count());
    }

    #[Test]
    public function it_can_get_a_module_based_on_where(): void
    {
        $slug = $this->repository->where('slug', 'repositorymod1');

        $this->assertInstanceOf(Collection::class, $slug);

        $this->assertCount(8, $slug);

        //

        $basename = $this->repository->where('basename', 'Repositorymod1');

        $this->assertInstanceOf(Collection::class, $basename);

        $this->assertCount(8, $basename);

        //

        $name = $this->repository->where('name', 'Repositorymod1');

        $this->assertInstanceOf(Collection::class, $name);

        $this->assertCount(8, $name);
    }

    #[Test]
    public function it_can_get_all_the_modules(): void
    {
        $this->assertCount(3, $this->repository->all());

        $this->assertInstanceOf(Collection::class, $this->repository->all());
    }

    #[Test]
    public function it_can_get_correct_module_and_manifest_for_legacy_modules(): void
    {
        $this->artisan('make:module', ['slug' => 'barbiz', '--quick' => 'quick']);

        // Quick and fast way to simulate legacy module folder structure
        // https://github.com/caffeinated/modules/pull/224
        rename(realpath(module_path('barbiz')), realpath(module_path()) . '/BarBiz');
        
        file_put_contents(realpath(module_path()) . '/BarBiz/module.json', json_encode([
            'name' => 'BarBiz', 'slug' => 'BarBiz', 'version' => '1.0', 'description' => '',
        ], JSON_PRETTY_PRINT));

        $this->assertSame(
            '{"name":"BarBiz","slug":"BarBiz","version":"1.0","description":""}',
            json_encode($this->repository->getManifest('BarBiz'))
        );

        $this->assertSame(
            realpath(module_path() . '/BarBiz'),
            realpath($this->repository->getModulePath('BarBiz'))
        );
    }

    #[Test]
    public function it_can_get_correct_slug_exists_for_legacy_modules(): void
    {
        $this->artisan('make:module', ['slug' => 'foobar', '--quick' => 'quick']);

        // Quick and fast way to simulate legacy Module FolderStructure
        // https://github.com/caffeinated/modules/pull/279
        // https://github.com/caffeinated/modules/pull/349
        rename(realpath(module_path('foobar')), realpath(module_path()) . '/FooBar');
        file_put_contents(realpath(module_path()) . '/FooBar/module.json', json_encode([
            'name' => 'FooBar', 'slug' => 'FooBar', 'version' => '1.0', 'description' => '',
        ], JSON_PRETTY_PRINT));

        $this->assertTrue($this->repository->exists('FooBar'));
    }

    #[Test]
    public function it_can_get_custom_modules_namespace(): void
    {
        $this->app['config']->set("modules.locations.{$this->default}.namespace", 'App\\Foo\\Bar\\Baz\\Tests');

        $this->assertSame('App\Foo\Bar\Baz\Tests', $this->repository->getNamespace());

        $this->app['config']->set("modules.locations.{$this->default}.namespace", 'App\\Foo\\Baz\\Bar\\Tests\\');

        $this->assertSame('App\Foo\Baz\Bar\Tests', $this->repository->getNamespace());
    }

    #[Test]
    public function it_can_get_default_modules_namespace(): void
    {
        $this->assertSame('App\Modules', $this->repository->getNamespace());
    }

    #[Test]
    public function it_can_get_default_modules_path(): void
    {
        $this->assertSame(base_path() . '/modules', $this->repository->getPath());
    }

    #[Test]
    public function it_can_get_manifest_of_module(): void
    {
        $manifest = $this->repository->getManifest('repositorymod1');

        $this->assertSame(
            '{"name":"Repositorymod1","slug":"repositorymod1","version":"1.0","description":"This is the description for the Repositorymod1 module."}',
            $manifest->toJson()
        );
    }

    #[Test]
    public function it_can_get_module_path_of_module(): void
    {
        $path = $this->repository->getModulePath('repositorymod1');

        $this->assertSame(
            base_path() . '/modules/Repositorymod1/',
            $path
        );
    }

    #[Test]
    public function it_can_get_property_of_module(): void
    {
        $this->assertSame('Repositorymod1', $this->repository->get('repositorymod1::name'));

        $this->assertSame('1.0', $this->repository->get('repositorymod2::version'));

        $this->assertSame('This is the description for the Repositorymod3 module.', $this->repository->get('repositorymod3::description'));
    }

    #[Test]
    public function it_can_get_the_modules_slugs(): void
    {
        $this->assertCount(3, $this->repository->slugs());

        $this->repository->slugs()->each(function ($key, $value): void {
            $this->assertSame('repositorymod' . ($value + 1), $key);
        });
    }

    #[Test]
    public function it_can_set_custom_modules_path_in_runtime_mode(): void
    {
        $this->repository->setPath(base_path('tests/runtime/modules'));

        $this->assertSame(
            base_path() . '/tests/runtime/modules',
            $this->repository->getPath()
        );
    }

    #[Test]
    public function it_can_set_property_of_module(): void
    {
        $this->assertSame('Repositorymod1', $this->repository->get('repositorymod1::name'));

        $this->repository->set('repositorymod1::name', 'FooBarRepositorymod1');

        $this->assertSame('FooBarRepositorymod1', $this->repository->get('repositorymod1::name'));

        //

        $this->assertSame('1.0', $this->repository->get('repositorymod3::version'));

        $this->repository->set('repositorymod3::version', '1.3.3.7');

        $this->assertSame('1.3.3.7', $this->repository->get('repositorymod3::version'));
    }

    #[Test]
    public function it_can_sortby_asc_slug_the_modules(): void
    {
        $sortByAsc = array_keys($this->repository->sortby('slug')->toArray());

        $this->assertSame($sortByAsc[0], 'Repositorymod1');
        $this->assertSame($sortByAsc[1], 'Repositorymod2');
        $this->assertSame($sortByAsc[2], 'Repositorymod3');
    }

    #[Test]
    public function it_can_sortby_desc_slug_the_modules(): void
    {
        $sortByAsc = array_keys($this->repository->sortbyDesc('slug')->toArray());

        $this->assertSame($sortByAsc[0], 'Repositorymod3');
        $this->assertSame($sortByAsc[1], 'Repositorymod2');
        $this->assertSame($sortByAsc[2], 'Repositorymod1');
    }

    /**
     * @expectedException \Exception
     */
    #[Test]
    public function it_will_throw_exception_by_invalid_json_manifest_file(): void
    {
        $this->expectException(\Exception::class);

        file_put_contents(realpath(module_path()) . '/Repositorymod1/module.json', 'invalidjsonformat');

        $manifest = $this->repository->getManifest('repositorymod1');
    }

    /**
     * @expectedException \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    #[Test]
    public function it_will_throw_file_not_found_exception_by_unknown_module(): void
    {
        $this->expectException(FileNotFoundException::class);
        
        $manifest = $this->repository->getManifest('unknown');
    }

    protected function tearDown(): void
    {
        $this->finder->deleteDirectory(module_path('repositorymod1'));

        $this->finder->deleteDirectory(module_path('repositorymod2'));

        $this->finder->deleteDirectory(module_path('repositorymod3'));

        $this->finder->deleteDirectory(module_path() . '/BarBiz');

        $this->finder->deleteDirectory(module_path() . '/FooBar');

        parent::tearDown();
    }
}