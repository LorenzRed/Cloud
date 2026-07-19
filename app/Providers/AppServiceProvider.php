<?php

namespace App\Providers;

use App\Models\ResidentRequest;
use App\Support\BhwResidentScope;
use App\Support\ResidentBhwChat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
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
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        View::composer(['sidebars.bhw', 'partials.bhw-mobile-nav'], function ($view) {
            $pendingRequestCount = 0;
            $requestMessageUnreadCount = 0;
            $residentMessageUnreadCount = 0;
            $user = Auth::user();

            if ($user && $user->role === 'BHW') {
                $pendingRequestCount = BhwResidentScope::apply(
                    ResidentRequest::query()->where('status', 'pending'),
                    $user
                )->count();
                $requestMessageUnreadCount = ResidentBhwChat::unreadCountForBhwByRequestStatus($user, 'pending');
                $residentMessageUnreadCount = ResidentBhwChat::unreadCountForBhwByRequestStatus($user, 'accepted');
            }

            $view->with([
                'pendingRequestCount' => $pendingRequestCount,
                'requestMessageUnreadCount' => $requestMessageUnreadCount,
                'residentMessageUnreadCount' => $residentMessageUnreadCount,
            ]);
        });

        View::composer(['sidebars.users', 'partials.users-mobile-nav'], function ($view) {
            $unreadBhwChatCount = 0;
            $user = Auth::user();

            if ($user && $user->role === 'users') {
                $user->loadMissing(['purok', 'residentRequest.purok']);
                $bhw = ResidentBhwChat::assignedBhwForResident($user);

                if ($bhw) {
                    $unreadBhwChatCount = ResidentBhwChat::unreadCountForResident($user, $bhw);
                }
            }

            $view->with('unreadBhwChatCount', $unreadBhwChatCount);
        });
    }
}
