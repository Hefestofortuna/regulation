<?php

namespace App\Providers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Auth\GenericUser;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['auth']->viaRequest('api', function ($request) {
            return new GenericUser((array)JWT::decode($_COOKIE['jwt'], new Key(env('SECRET_KEY'), env('ALG')))->data);
        });
    }
}
