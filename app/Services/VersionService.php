<?php

namespace App\Services;

use Exception;
use App\Models\Version;
use Illuminate\Support\Str;
use App\Services\BaseService;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\VersionResource;
use App\Http\Resources\VersionResources;
use App\Models\App;

class VersionService extends BaseService
{
    /**
     * Show versions.
     *
     * @param App $app
     * @param array $data
     * @return VersionResources|array
     */
    public function showVersions(App $app, array $data): VersionResources|array
    {
        $query = Version::where('app_id', $app->id);
        if(!request()->has('_sort')) $query = $query->latest();

        return $this->setQuery($query)->getOutput();
    }

    /**
     * Create version.
     *
     * @param App $app
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function createVersion(App $app, array $data): array
    {
        /** @var User $user */
        $user = Auth::user();

        $versionId = $data['clone_from_id'] ?? null;

        if ($versionId) {
            $builder = Version::findOrFail($versionId)->builder;
        } else {
            $builder = $this->getBuilderTemplate();
        }

        $version = Version::create([
            ...$data,
            'app_id' => $app->id,
            'builder' => $builder,
            'created_by' => $user->id,
        ]);

        return $this->showCreatedResource($version);
    }

    /**
     * Delete Versions.
     *
     * @param App $app
     * @param array $versionIds
     * @return array
     * @throws Exception
     */
    public function deleteVersions(App $app, array $versionIds): array
    {
        $versions = Version::whereIn('id', $versionIds)->get();

        if($totalVersions = $versions->count()) {

            foreach ($versions as $version) {

                $this->deleteVersion($app, $version);

            }

            return ['message' => $totalVersions  . ($totalVersions  == 1 ? ' Version': ' Versions') . ' deleted'];

        }else{

            throw new Exception('No Versions deleted');

        }
    }

    /**
     * Show version.
     *
     * @param App $app
     * @param Version $version
     * @return VersionResource
     */
    public function showVersion(App $app, Version $version): VersionResource
    {
        return $this->showResource($version);
    }

    /**
     * Update version.
     *
     * @param App $app
     * @param Version $version
     * @param array $data
     * @return array
     */
    public function updateVersion(App $app, Version $version, array $data): array
    {
        $version->update($data);
        return $this->showUpdatedResource($version);
    }

    /**
     * Delete version.
     *
     * @param App $app
     * @param Version $version
     * @return array
     */
    public function deleteVersion(App $app, Version $version): array
    {
        $deleted = $version->forceDelete();

        return [
            'deleted' => $deleted,
            'message' => $deleted ? 'Version deleted' : 'Version delete unsuccessful'
        ];
    }

    /**
     * Get builder template (normalized: steps, features, validation_rules).
     *
     * @return array
     */
    private function getBuilderTemplate(): array
    {
        return [
            'steps' => [],
            'features' => [],
            'initial_step_id' => null,
            'validation_rules' => (object) [],
        ];
    }
}
