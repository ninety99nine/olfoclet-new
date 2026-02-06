<?php

namespace App\Services;

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
     * @param App $app
     * @param array $data
     * @return DeploymentResources|array
     */
    public function showDeployments(App $app, array $data): DeploymentResources|array
    {
        /** @var User $user */
        $user = Auth::user();

        $country = $data['country'] ?? null;
        $network = $data['network'] ?? null;
        $active = isset($data['active']) ?? null;

        $query = Deployment::where('app_id', $app->id);

        if (!empty($country)) $query->where('country', $country);
        if (!empty($network)) $query->where('network', $network);
        if ($active !== null) $query->where('active', $active);

        if (!request()->has('_sort')) $query = $query->latest();

        return $this->setQuery($query)->getOutput();
    }

    /**
     * Create deployment.
     *
     * @param App $app
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function createDeployment(App $app, array $data): array
    {
        $deployment = Deployment::create([
            ...$data,
            'app_id' => $app->id
        ]);

        return $this->showCreatedResource($deployment);
    }

    /**
     * Show deployment.
     *
     * @param App $app
     * @param Deployment $deployment
     * @return DeploymentResource
     */
    public function showDeployment(App $app, Deployment $deployment): DeploymentResource
    {
        return $this->showResource($deployment);
    }

    /**
     * Update deployment.
     *
     * @param App $app
     * @param Deployment $deployment
     * @param array $data
     * @return array
     */
    public function updateDeployment(App $app, Deployment $deployment, array $data): array
    {
        $deployment->update($data);

        return $this->showUpdatedResource($deployment);
    }

    /**
     * Delete deployments.
     *
     * @param App $app
     * @param array $deploymentIds
     * @return array
     * @throws Exception
     */
    public function deleteDeployments(App $app, array $deploymentIds): array
    {
        $deployments = Deployment::where('app_id', $app->id)->whereIn('id', $deploymentIds)->get();

        if ($total = $deployments->count()) {
            foreach ($deployments as $deployment) {
                $this->deleteDeployment($app, $deployment);
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
     * @param App $app
     * @param Deployment $deployment
     * @return array
     * @throws Exception
     */
    public function deleteDeployment(App $app, Deployment $deployment): array
    {
        $deleted = $deployment->delete();

        return [
            'deleted' => $deleted,
            'message' => $deleted ? 'Deployment deleted' : 'Deployment delete unsuccessful'
        ];
    }
}
