<?php

namespace App\Http\Controllers;

use App\Models\Deployment;
use App\Services\DeploymentService;
use App\Http\Resources\DeploymentResource;
use App\Http\Resources\DeploymentResources;
use App\Http\Requests\Deployment\ShowDeploymentRequest;
use App\Http\Requests\Deployment\ShowDeploymentsRequest;
use App\Http\Requests\Deployment\CreateDeploymentRequest;
use App\Http\Requests\Deployment\UpdateDeploymentRequest;
use App\Http\Requests\Deployment\DeleteDeploymentRequest;
use App\Http\Requests\Deployment\DeleteDeploymentsRequest;
use App\Models\App;

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
     * @param App $app
     * @return DeploymentResources|array
     */
    public function showDeployments(ShowDeploymentsRequest $request, App $app): DeploymentResources|array
    {
        return $this->service->showDeployments($app, $request->validated());
    }

    /**
     * Create deployment.
     *
     * @param CreateDeploymentRequest $request
     * @param App $app
     * @return array
     */
    public function createDeployment(CreateDeploymentRequest $request, App $app): array
    {
        return $this->service->createDeployment($app, $request->validated());
    }

    /**
     * Delete deployments.
     *
     * @param DeleteDeploymentsRequest $request
     * @param App $app
     * @return array
     */
    public function deleteDeployments(DeleteDeploymentsRequest $request, App $app): array
    {
        $deploymentIds = $request->input('deployment_ids', []);
        return $this->service->deleteDeployments($app, $deploymentIds);
    }

    /**
     * Show deployment.
     *
     * @param ShowDeploymentRequest $request
     * @param Deployment $deployment
     * @return DeploymentResource
     */
    public function showDeployment(ShowDeploymentRequest $request, App $app, Deployment $deployment): DeploymentResource
    {
        return $this->service->showDeployment($app, $deployment);
    }

    /**
     * Update deployment.
     *
     * @param UpdateDeploymentRequest $request
     * @param App $app
     * @param Deployment $deployment
     * @return array
     */
    public function updateDeployment(UpdateDeploymentRequest $request, App $app, Deployment $deployment): array
    {
        return $this->service->updateDeployment($app, $deployment, $request->validated());
    }

    /**
     * Delete deployment.
     *
     * @param DeleteDeploymentRequest $request
     * @param App $app
     * @param Deployment $deployment
     * @return array
     */
    public function deleteDeployment(DeleteDeploymentRequest $request, App $app, Deployment $deployment): array
    {
        return $this->service->deleteDeployment($app, $deployment);
    }
}
