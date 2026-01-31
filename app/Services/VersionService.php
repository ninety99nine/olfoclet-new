<?php

namespace App\Services;

use Exception;
use App\Models\Version;
use Illuminate\Support\Str;
use App\Services\BaseService;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\VersionResource;
use App\Http\Resources\VersionResources;

class VersionService extends BaseService
{
    /**
     * Show versions.
     *
     * @param array $data
     * @return VersionResources|array
     */
    public function showVersions(array $data): VersionResources|array
    {
        $appId = $data['app_id'] ?? null;

        $query = Version::where('app_id', $appId);
        if(!request()->has('_sort')) $query = $query->latest();

        return $this->setQuery($query)->getOutput();
    }

    /**
     * Create version.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function createVersion(array $data): array
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
            'builder' => $builder,
            'created_by' => $user->id
        ]);



        return $this->showCreatedResource($version);
    }

    /**
     * Delete Versions.
     *
     * @param array $versionIds
     * @return array
     * @throws Exception
     */
    public function deleteVersions(array $versionIds): array
    {
        $versions = Version::whereIn('id', $versionIds)->get();

        if($totalVersions = $versions->count()) {

            foreach ($versions as $version) {

                $this->deleteVersion($version);

            }

            return ['message' => $totalVersions  . ($totalVersions  == 1 ? ' Version': ' Versions') . ' deleted'];

        }else{

            throw new Exception('No Versions deleted');

        }
    }

    /**
     * Show version.
     *
     * @param Version $version
     * @return VersionResource
     */
    public function showVersion(Version $version): VersionResource
    {
        return $this->showResource($version);
    }

    /**
     * Update version.
     *
     * @param Version $version
     * @param array $data
     * @return array
     */
    public function updateVersion(Version $version, array $data): array
    {
        $version->update($data);
        return $this->showUpdatedResource($version);
    }

    /**
     * Delete version.
     *
     * @param Version $version
     * @return array
     */
    public function deleteVersion(Version $version): array
    {
        $deleted = $version->forceDelete();

        return [
            'deleted' => $deleted,
            'message' => $deleted ? 'Version deleted' : 'Version delete unsuccessful'
        ];
    }

    /**
     * Get builder template.
     *
     * @return array
     */
    public function getBuilderTemplate(): array
    {
        $id = (string) Str::uuid();

        return [
            'steps' => [
                $id => [
                    'type' => 'special',
                    'position' => [
                        'x' => 40,
                        'y' => 60
                    ],
                    'data' => [
                        'name' => 'Main Menu'
                    ]
                ]
            ]
        ];
    }
}
