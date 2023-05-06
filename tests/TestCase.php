<?php

namespace Amohamed\Laravelmodelmaker\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Amohamed\Laravelmodelmaker\LaravelModelMakerServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // Load the .env.testing file
        $this->app->loadEnvironmentFrom('.env.testing');
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelModelMakerServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Set the test environment database configuration
        $app['config']->set('database.connections.mysql', [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => 3306,
            'database' => 'test_package',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]);
    }
}
