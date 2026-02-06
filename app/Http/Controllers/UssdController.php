<?php

namespace App\Http\Controllers;

use App\Models\Deployment;
use Illuminate\Http\Request;
use App\Services\UssdService;
use App\Http\Requests\Ussd\SimulateUssdRequest;

class UssdController extends Controller
{
    /**
     * @var UssdService
     */
    protected $service;

    /**
     * UssdController constructor.
     *
     * @param UssdService $service
     */
    public function __construct(UssdService $service)
    {
        $this->service = $service;
    }

    /**
     * Simulate USSD.
     *
     * @param SimulateUssdRequest $request
     * @return array
     */
    public function simulateUssd(SimulateUssdRequest $request): array
    {
        return $this->service->simulateUssd($request->validated());
    }

    /**
     * Launch USSD.
     *
     * @param Request $request
     * @param Deployment $deployment
     * @return mixed
     */
    public function launchUssd(Request $request, Deployment $deployment): mixed
    {
        return $this->service->launchUssd($request, $deployment);
    }
}
