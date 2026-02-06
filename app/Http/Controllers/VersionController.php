<?php

namespace App\Http\Controllers;

use App\Models\Version;
use App\Services\VersionService;
use App\Http\Resources\VersionResource;
use App\Http\Resources\VersionResources;
use App\Http\Requests\Version\ShowVersionRequest;
use App\Http\Requests\Version\ShowVersionsRequest;
use App\Http\Requests\Version\CreateVersionRequest;
use App\Http\Requests\Version\UpdateVersionRequest;
use App\Http\Requests\Version\DeleteVersionRequest;
use App\Http\Requests\Version\DeleteVersionsRequest;
use App\Models\App;

class VersionController extends Controller
{
    /**
     * @var VersionService
     */
    protected $service;

    /**
     * VersionController constructor.
     *
     * @param VersionService $service
     */
    public function __construct(VersionService $service)
    {
        $this->service = $service;
    }

    /**
     * Show versions.
     *
     * @param ShowVersionsRequest $request
     * @return VersionResources|array
     */
    public function showVersions(ShowVersionsRequest $request, App $app): VersionResources|array
    {
        return $this->service->showVersions($app, $request->validated());
    }

    /**
     * Create version.
     *
     * @param CreateVersionRequest $request
     * @param App $app
     * @return array
     */
    public function createVersion(CreateVersionRequest $request, App $app): array
    {
        return $this->service->createVersion($app, $request->validated());
    }

    /**
     * Delete versions.
     *
     * @param DeleteVersionsRequest $request
     * @param App $app
     * @return array
     */
    public function deleteVersions(DeleteVersionsRequest $request, App $app): array
    {
        $versionIds = request()->input('version_ids', []);
        return $this->service->deleteVersions($app, $versionIds);
    }

    /**
     * Show version.
     *
     * @param ShowVersionRequest $request
     * @param App $app
     * @param Version $version
     * @return VersionResource
     */
    public function showVersion(ShowVersionRequest $request, App $app, Version $version): VersionResource
    {
        return $this->service->showVersion($app, $version);
    }

    /**
     * Update version.
     *
     * @param UpdateVersionRequest $request
     * @param App $app
     * @param Version $version
     * @return array
     */
    public function updateVersion(UpdateVersionRequest $request, App $app, Version $version): array
    {
        return $this->service->updateVersion($app, $version, $request->validated());
    }

    /**
     * Delete version.
     *
     * @param DeleteVersionRequest $request
     * @param App $app
     * @param Version $version
     * @return array
     */
    public function deleteVersion(DeleteVersionRequest $request, App $app, Version $version): array
    {
        return $this->service->deleteVersion($app, $version);
    }
}
