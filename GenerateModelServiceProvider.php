<?php

namespace cardosso4\GenerateModel;

use cardosso4\GenerateModel\Commands\CreateModel;
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