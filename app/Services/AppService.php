<?php

namespace App\Services;

use Exception;
use App\Models\App;
use App\Models\User;
use App\Models\Role;
use App\Enums\Association;
use App\Enums\UploadFolderName;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\AppResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\AppResources;
use App\Models\Deployment;

class AppService extends BaseService
{
    /**
     * Show apps.
     *
     * @param array $data
     * @return AppResources|array
     */
    public function showApps(array $data): AppResources|array
    {
        /** @var User $user */
        $user = Auth::user();
        $association = isset($data['association']) ? Association::tryFrom($data['association']) : null;

        if ($association === Association::SUPER_ADMIN) {
            $query = App::query()->latest();
        } else {
            $query = $user->apps();
            if(!request()->has('_sort')) $query = $query->orderByPivot('last_seen_at', 'desc');
        }

        return $this->setQuery($query)->getOutput();
    }

    /**
     * Create app.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function createApp(array $data): array
    {
        /** @var User $user */
        $user = Auth::user();

        // 1. Create the app
        $app = App::create($data);

        // 2. Create the deployment
        $app->deployments()->create($data);

        // 3. Create app-scoped roles
        $adminRole = Role::create([
            'name'       => 'admin',
            'guard_name' => 'sanctum',
            'app_id'   => $app->id,
        ]);

        $developerRole = Role::create([
            'guard_name' => 'sanctum',
            'name'       => 'developer',
            'app_id'   => $app->id,
        ]);

        // 4. Assign as app team member with admin role attached (for per-app accuracy)
        $app->users()->attach($user->id, [
            'creator' => true,
            'joined_at' => now(),
            'invited_at' => null,
            'last_seen_at' => now(),
            'role_id' => $adminRole->id
        ]);

        // 5. Upload logo if provided
        if (!empty($data['logo'] ?? null)) {
            (new MediaFileService)->createMediaFile([
                'file'              => $data['logo'],
                'mediable_type'     => 'app',
                'mediable_id'       => $app->id,
                'upload_folder_name'=> UploadFolderName::APP_LOGO->value
            ]);
        }

        return $this->showCreatedResource($app);
    }

    /**
     * Delete Apps.
     *
     * @param array $appIds
     * @return array
     * @throws Exception
     */
    public function deleteApps(array $appIds): array
    {
        $apps = App::whereIn('id', $appIds)->with(['mediaFiles'])->get();

        if($totalApps = $apps->count()) {

            foreach ($apps as $app) {

                $this->deleteApp($app);

            }

            return ['message' => $totalApps  . ($totalApps  == 1 ? ' App': ' Apps') . ' deleted'];

        }else{

            throw new Exception('No Apps deleted');

        }
    }

    /**
     * Show app.
     *
     * @param App $app
     * @return AppResource
     */
    public function showApp(App $app): AppResource
    {
        return $this->showResource($app);
    }

    /**
     * Update app.
     *
     * @param App $app
     * @param array $data
     * @return array
     */
    public function updateApp(App $app, array $data): array
    {
        $app->update($data);
        return $this->showUpdatedResource($app);
    }

    /**
     * Delete app.
     *
     * @param App $app
     * @return array
     * @throws Exception
     */
    public function deleteApp(App $app): array
    {
        $mediaFileService = new MediaFileService;

        foreach ($app->mediaFiles as $mediaFile) {
            $mediaFileService->deleteMediaFile($mediaFile);
        }

        $deleted = $app->forceDelete();

        return [
            'deleted' => $deleted,
            'message' => $deleted ? 'App deleted' : 'App delete unsuccessful'
        ];
    }

    /**
     * Accept app invitations.
     *
     * @param array $data
     * @return array
     */
    public function acceptAppInvitations(User $user)
    {
        DB::table('store_user')->where('email', $user->email)->update([
            'email' => null,
            'joined_at' => now(),
            'first_name' => null,
            'user_id' => $user->id
        ]);
    }
}
