<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Sale; // أضيفي هذا السطر
use App\Observers\SaleObserver; // أضيفي هذا السطر

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Sale::observe(SaleObserver::class);
    }
}