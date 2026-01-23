<?php

namespace App\Http\Controllers;

use App\Services\MiscellaneousService;
use App\Http\Requests\Miscellaneous\ShowFiltersRequest;
use App\Http\Requests\Miscellaneous\ShowSortingRequest;

class MiscellaneousController extends Controller
{
    /**
     * @var MiscellaneousService
     */
    protected $service;

    /**
     * MiscellaneousController constructor.
     *
     * @param MiscellaneousService $service
     */
    public function __construct(MiscellaneousService $service)
    {
        $this->service = $service;
    }

    /**
     * Show filters.
     *
     * @param ShowFiltersRequest $request
     * @return JsonResponse
     */
    public function showFilters(ShowFiltersRequest $request): array
    {
        return $this->service->showFilters($request->validated());
    }
}
