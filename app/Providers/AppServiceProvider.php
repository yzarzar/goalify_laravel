<?php

namespace App\Providers;

use App\Models\Milestone;
use App\Observers\MilestoneObserver;
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
        Milestone::observe(MilestoneObserver::class);
    }
}
