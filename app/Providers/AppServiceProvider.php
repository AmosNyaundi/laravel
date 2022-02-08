<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;


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

        View::share(['appName' => 'EazyCredo', 'year' => $year]);
        Schema::defaultStringLength(191);
    }
}
