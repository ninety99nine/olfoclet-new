<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\App;
use App\Models\User;
use App\Models\AppUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetLastSeenOnApp
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Proceed with the request first to allow model route binding to resolve the app
        $response = $next($request);

        /** @var User $user */
        $user = Auth::user();

        /** @var App $app */
        $app = $request->route('app');

        AppUser::where([
            'user_id' => $user->id,
            'app_id' => $app->id
        ])->update(
            [
                'last_seen_at' => now()
            ]
        );

        return $response;
    }
}
