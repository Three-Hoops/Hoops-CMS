<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;                                                                                                                                                                                                                             
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! app()->isProduction());
        Password::defaults(function () {
            return app()->isProduction()
                ? Password::min(12)->mixedCase()->numbers()->symbols()->uncompromised()
                : Password::min(8);
        });
    }
}
