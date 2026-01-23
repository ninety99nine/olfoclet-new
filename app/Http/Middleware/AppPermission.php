<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\AuthService;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;

class AppPermission
{
    /**
     * @var AuthService
     */
    protected $authService;

    /**
     * Create a new middleware instance.
     *
     * @param AuthService $authService
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next, $mode = 'required'): Response
    {
        $isRequired = $mode == 'required';
        $user = $request->user('sanctum');

        if(!$user && $isRequired) throw new AuthenticationException;

        $appId = $request->route('app') ?? $request->input('app_id');

        $isSuperAdmin = $user ? $this->authService->isSuperAdmin($user) : false;

        if($appId && !$isSuperAdmin) {

            // Set the app id as the team id for Spatie's permission checks
            setPermissionsTeamId($appId);

        }else if(!$appId && !$isSuperAdmin && $isRequired) {

            return response()->json(['message' => 'The app ID is required'], Response::HTTP_UNPROCESSABLE_ENTITY);

        }

        return $next($request);

    }
}
