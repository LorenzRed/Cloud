<?php

namespace App\Providers;

use App\Models\ResidentRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
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
        View::composer('sidebars.bhw', function ($view) {
            $pendingRequestCount = 0;
            $user = Auth::user();

            if ($user && $user->role === 'BHW') {
                $pendingRequestCount = ResidentRequest::query()
                    ->where('status', 'pending')
                    ->count();
            }

            $view->with('pendingRequestCount', $pendingRequestCount);
        });
    }
}
