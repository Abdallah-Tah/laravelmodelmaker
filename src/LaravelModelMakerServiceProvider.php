<?php

namespace Amohamed\Laravelmodelmaker;

use Illuminate\Support\ServiceProvider;
use Amohamed\Laravelmodelmaker\Console\Commands\GenerateModel;
use Amohamed\Laravelmodelmaker\Console\Commands\GenerateModelWithRelations;
use Amohamed\Laravelmodelmaker\Console\Commands\GenerateRelationshipCommand;

/**
 * Class LaravelModelMakerServiceProvider
 * @package Amohamed\Laravelmodelmaker
 * @Author Abdallah Mohamed
 */
class LaravelModelMakerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateModel::class,
                GenerateModelWithRelations::class,
                GenerateRelationshipCommand::class,
            ]);
        }
    }


    public function register()
    {
        //
    }
}
