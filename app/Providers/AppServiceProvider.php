<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        // if (app()->environment('production')) {
        //     URL::forceScheme('https');
        // }
        Paginator::useBootstrapFive();

        // Dynamically configure session lifetime and timezone from database
        try {
            if (Schema::hasTable('pengaturan_umum')) {
                $setting = DB::table('pengaturan_umum')->first();
                if ($setting) {
                    if (!empty($setting->session_time)) {
                        // 1 day = 1440 minutes
                        config(['session.lifetime' => $setting->session_time * 1440]);
                    }
                    if (!empty($setting->timezone)) {
                        config(['app.timezone' => $setting->timezone]);
                        date_default_timezone_set($setting->timezone);
                    }
                }
            }
        } catch (\Exception $e) {
            // Prevent failure during migrations or DB not connected
        }
    }
}
