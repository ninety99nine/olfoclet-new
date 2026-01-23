<?php

namespace App\Services;

use App\Enums\Association;
use Exception;
use App\Models\App;
use App\Models\Deployment;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\DeploymentResource;
use App\Http\Resources\DeploymentResources;

class DeploymentService extends BaseService
{
    /**
     * Show deployments.
     *
     * @param array $data
     * @return DeploymentResources|array
     */
    public function showDeployments(array $data): DeploymentResources|array
    {
        /** @var User $user */
        $user = Auth::user();

        $appId = $data['app_id'] ?? null;
        $country = $data['country'] ?? null;
        $network = $data['network'] ?? null;
        $active = isset($data['active']) ?? null;
        $association = isset($data['association']) ? Association::tryFrom($data['association']) : null;

        if ($association === Association::SUPER_ADMIN) {
            $query = Deployment::query()->latest();
        } else if (!empty($appId)) {
            $query = Deployment::where('app_id', $appId);
        } else {
            $appIds = $user->apps()->pluck('apps.id');
            $query = Deployment::whereIn('app_id', $appIds);
        }

        if (!empty($country)) $query->where('country', $country);
        if (!empty($network)) $query->where('network', $network);
        if ($active !== null) $query->where('active', $active);

        if (!request()->has('_sort')) $query = $query->latest();

        return $this->setQuery($query)->getOutput();
    }

    /**
     * Create deployment.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function createDeployment(array $data): array
    {
        $deployment = Deployment::create($data);

        return $this->showCreatedResource($deployment);
    }

    /**
     * Show deployment.
     *
     * @param Deployment $deployment
     * @return DeploymentResource
     */
    public function showDeployment(Deployment $deployment): DeploymentResource
    {
        return $this->showResource($deployment);
    }

    /**
     * Update deployment.
     *
     * @param Deployment $deployment
     * @param array $data
     * @return array
     */
    public function updateDeployment(Deployment $deployment, array $data): array
    {
        $deployment->update($data);

        return $this->showUpdatedResource($deployment);
    }

    /**
     * Delete multiple deployments.
     *
     * @param array $deploymentIds
     * @return array
     * @throws Exception
     */
    public function deleteDeployments(array $deploymentIds): array
    {
        $deployments = Deployment::whereIn('id', $deploymentIds)->get();

        if ($total = $deployments->count()) {
            foreach ($deployments as $deployment) {
                $this->deleteDeployment($deployment);
            }

            return [
                'message' => $total . ($total === 1 ? ' deployment' : ' deployments') . ' deleted'
            ];
        }

        throw new Exception('No deployments deleted');
    }

    /**
     * Delete deployment.
     *
     * @param Deployment $deployment
     * @return array
     * @throws Exception
     */
    public function deleteDeployment(Deployment $deployment): array
    {
        $deleted = $deployment->delete();

        return [
            'deleted' => $deleted,
            'message' => $deleted ? 'Deployment deleted' : 'Deployment delete unsuccessful'
        ];
    }
}
