<?php

namespace Cardosso4\GenerateModel;

use Cardosso4\GenerateModel\GenerateModel\Commands\CreateModel;
use Illuminate\Support\ServiceProvider;

class GenerateModelServiceProvider extends ServiceProvider
{

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateModel::class,
            ]);
        }
    }

}