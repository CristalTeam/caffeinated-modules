<?php

namespace Caffeinated\Modules\Tests;

use PHPUnit\Framework\Attributes\Test;
use Module;

final class FacadeTest extends BaseTestCase
{
    #[Test]
    public function it_can_work_with_container(): void
    {
        $this->assertInstanceOf(\Caffeinated\Modules\RepositoryManager::class, $this->app['modules']);
    }

    #[Test]
    public function it_can_work_with_facade(): void
    {
        $this->assertSame('Caffeinated\Modules\Facades\Module', (new \ReflectionClass(Module::class))->getName());
    }
}