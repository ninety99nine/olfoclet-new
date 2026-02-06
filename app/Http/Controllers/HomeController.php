<?php

namespace App\Http\Controllers;

use App\Services\HomeService;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    /**
     * @var HomeService
     */
    protected $service;

    /**
     * HomeController constructor.
     *
     * @param HomeService $service
     */
    public function __construct(HomeService $service)
    {
        $this->service = $service;
    }

    /**
     * Show ussd accounts summary.
     *
     * @return JsonResponse
     */
    public function showHomeSummary(): JsonResponse
    {
        return response()->json(
            $this->service->showHomeSummary()
        );
    }
}
