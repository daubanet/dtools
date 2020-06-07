<?php

namespace Daubanet\DTools\Tests;

use Orchestra\Testbench\TestCase;
use Daubanet\DTools\DToolsServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [DToolsServiceProvider::class];
    }
    
    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
