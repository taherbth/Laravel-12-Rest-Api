<?php
namespace App\Providers;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

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
        // Define a named rate limiter 'api'
        RateLimiter::for('api-useryo', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
        // Stricter limiter for sensitive routes like login
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email') . '|' . $request->ip());
        });
        // Generous limit for public, read-only common resources
        RateLimiter::for('public-resources', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });
        RateLimiter::for('subscription-tier', function (Request $request) {
            $user = $request->user();
            // Fallback for guests/unauthenticated requests
            if (! $user) {
                return Limit::perMinute(30)->by($request->ip());
            }

            // Assign limits based on user's tier
            $maxAttempts = match ($user->subscription_tier) { // e.g., 'premium', 'free'
                'premium' => 500, // 500 requests per minute
                'pro'     => 200, // 200 requests per minute
                default   => 60,  // Free tier default
            };

            return Limit::perMinute($maxAttempts)->by($user->id);
        });
    }
}
