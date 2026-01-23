<?php

namespace App\Http\Controllers;

use App\Models\App;
use App\Services\AppService;
use App\Http\Resources\AppResource;
use App\Http\Resources\AppResources;
use App\Http\Requests\App\ShowAppRequest;
use App\Http\Requests\App\ShowAppsRequest;
use App\Http\Requests\App\CreateAppRequest;
use App\Http\Requests\App\UpdateAppRequest;
use App\Http\Requests\App\DeleteAppRequest;
use App\Http\Requests\App\DeleteAppsRequest;

class AppController extends Controller
{
    /**
     * @var AppService
     */
    protected $service;

    /**
     * AppController constructor.
     *
     * @param AppService $service
     */
    public function __construct(AppService $service)
    {
        $this->service = $service;
    }

    /**
     * Show apps.
     *
     * @param ShowAppsRequest $request
     * @return AppResources|array
     */
    public function showApps(ShowAppsRequest $request): AppResources|array
    {
        return $this->service->showApps($request->validated());
    }

    /**
     * Create app.
     *
     * @param CreateAppRequest $request
     * @return array
     */
    public function createApp(CreateAppRequest $request): array
    {
        return $this->service->createApp($request->validated());
    }

    /**
     * Delete apps.
     *
     * @param DeleteAppsRequest $request
     * @return array
     */
    public function deleteApps(DeleteAppsRequest $request): array
    {
        $appIds = request()->input('app_ids', []);
        return $this->service->deleteApps($appIds);
    }

    /**
     * Show app.
     *
     * @param ShowAppRequest $request
     * @param App $app
     * @return AppResource
     */
    public function showApp(ShowAppRequest $request, App $app): AppResource
    {
        return $this->service->showApp($app);
    }

    /**
     * Update app.
     *
     * @param UpdateAppRequest $request
     * @param App $app
     * @return array
     */
    public function updateApp(UpdateAppRequest $request, App $app): array
    {
        return $this->service->updateApp($app, $request->validated());
    }

    /**
     * Delete app.
     *
     * @param DeleteAppRequest $request
     * @param App $app
     * @return array
     */
    public function deleteApp(DeleteAppRequest $request, App $app): array
    {
        return $this->service->deleteApp($app);
    }
}
