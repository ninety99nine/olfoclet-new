<?php

namespace App\Http\Controllers;

use App\Models\Deployment;
use App\Services\DeploymentService;
use App\Http\Resources\DeploymentResource;
use App\Http\Resources\DeploymentResources;
use App\Http\Requests\Deployment\ShowDeploymentsRequest;
use App\Http\Requests\Deployment\ShowDeploymentRequest;
use App\Http\Requests\Deployment\CreateDeploymentRequest;
use App\Http\Requests\Deployment\UpdateDeploymentRequest;
use App\Http\Requests\Deployment\DeleteDeploymentRequest;
use App\Http\Requests\Deployment\DeleteDeploymentsRequest;

class DeploymentController extends Controller
{
    /**
     * @var DeploymentService
     */
    protected $service;

    /**
     * DeploymentController constructor.
     *
     * @param DeploymentService $service
     */
    public function __construct(DeploymentService $service)
    {
        $this->service = $service;
    }

    /**
     * Show deployments.
     *
     * @param ShowDeploymentsRequest $request
     * @return DeploymentResources|array
     */
    public function showDeployments(ShowDeploymentsRequest $request): DeploymentResources|array
    {
        return $this->service->showDeployments($request->validated());
    }

    /**
     * Create deployment.
     *
     * @param CreateDeploymentRequest $request
     * @return array
     */
    public function createDeployment(CreateDeploymentRequest $request): array
    {
        return $this->service->createDeployment($request->validated());
    }

    /**
     * Delete deployments.
     *
     * @param DeleteDeploymentsRequest $request
     * @return array
     */
    public function deleteDeployments(DeleteDeploymentsRequest $request): array
    {
        $deploymentIds = $request->input('deployment_ids', []);
        return $this->service->deleteDeployments($deploymentIds);
    }

    /**
     * Show deployment.
     *
     * @param ShowDeploymentRequest $request
     * @param Deployment $deployment
     * @return DeploymentResource
     */
    public function showDeployment(ShowDeploymentRequest $request, Deployment $deployment): DeploymentResource
    {
        return $this->service->showDeployment($deployment);
    }

    /**
     * Update deployment.
     *
     * @param UpdateDeploymentRequest $request
     * @param Deployment $deployment
     * @return array
     */
    public function updateDeployment(UpdateDeploymentRequest $request, Deployment $deployment): array
    {
        return $this->service->updateDeployment($deployment, $request->validated());
    }

    /**
     * Delete deployment.
     *
     * @param DeleteDeploymentRequest $request
     * @param Deployment $deployment
     * @return array
     */
    public function deleteDeployment(DeleteDeploymentRequest $request, Deployment $deployment): array
    {
        return $this->service->deleteDeployment($deployment);
    }
}
