<?php

namespace App\Providers;

use App\Models\User;
use App\Models\App;
use App\Listeners\RoleEventListener;
use App\Models\Deployment;
use App\Models\UssdAccount;
use App\Models\UssdSession;
use App\Models\UssdSessionFlag;
use App\Models\UssdSessionStep;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;

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
        Relation::enforceMorphMap([
            'app' => 'App\Models\App',
            'user' => 'App\Models\User',

        ]);

        JsonResource::withoutWrapping();

        $this->explicitRouteModelBiniding();

        //  Events
        Event::subscribe(RoleEventListener::class);
    }

    /**
     *  Customize how we resolve route model bindings.
     *
     *  Reference: https://laravel.com/docs/12.x/routing#customizing-the-resolution-logic
     */
    private function explicitRouteModelBiniding()
    {
        // Bind User model
        Route::bind('user', function ($value) {
            $allowedRoutes = ['show.user',];
            return $this->applyEagerLoading(User::query(), $allowedRoutes)->findOrFail($value);
        });

        // Bind App model
        Route::bind('app', function ($value) {
            $allowedRoutes = ['show.app'];
            return $this->applyEagerLoading(App::query(), $allowedRoutes)->findOrFail($value);
        });

        // Bind Deployment model
        Route::bind('deployment', function ($value) {
            $allowedRoutes = ['show.deployment'];
            return $this->applyEagerLoading(Deployment::query(), $allowedRoutes)->findOrFail($value);
        });

        // Bind UssdAccount model
        Route::bind('ussd_account', function ($value) {
            $allowedRoutes = ['show.ussd.account'];
            return $this->applyEagerLoading(UssdAccount::query(), $allowedRoutes)->findOrFail($value);
        });

        // Bind UssdSession model
        Route::bind('ussd_session', function ($value) {
            $allowedRoutes = ['show.ussd.session'];
            return $this->applyEagerLoading(UssdSession::query(), $allowedRoutes)->findOrFail($value);
        });

        // Bind UssdSessionStep model
        Route::bind('ussd_session_step', function ($value) {
            $allowedRoutes = ['show.ussd.session.step'];
            return $this->applyEagerLoading(UssdSessionStep::query(), $allowedRoutes)->findOrFail($value);
        });

        // Bind UssdSessionFlag model
        Route::bind('ussd_session_flag', function ($value) {
            $allowedRoutes = ['show.ussd.session.flag'];
            return $this->applyEagerLoading(UssdSessionFlag::query(), $allowedRoutes)->findOrFail($value);
        });
    }

    /**
     * Apply eager loading to a query based on request parameters, only for allowed routes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $allowedRoutes Route names where eager loading should apply
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function applyEagerLoading($query, array $allowedRoutes = [])
    {
        // Skip eager loading if current route is not in allowed routes
        if (!in_array(Route::currentRouteName(), $allowedRoutes)) {
            return $query;
        }

        $relationships = array_filter(array_map('trim', explode(',', request()->input('_relationships', ''))));
        $countableRelationships = array_filter(array_map('trim', explode(',', request()->input('_countable_relationships', ''))));

        if (!empty($relationships)) {
            $query->with($relationships);
        }

        if (!empty($countableRelationships)) {
            $query->withCount($countableRelationships);
        }

        return $query;
    }
}
