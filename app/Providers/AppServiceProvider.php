<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
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
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $year = Carbon::today('Africa/Nairobi')->format('Y');
       /// $settings = Settings::first();
        View::share(['appName' => 'Train', 'year' => $year]);
    }
}
