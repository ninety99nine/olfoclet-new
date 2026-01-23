<?php

namespace App\Services;

use App\Models\App;
use App\Enums\FilterResourceType;

class MiscellaneousService
{
    /**
     * Show filters.
     *
     * @param array $data
     * @return array
     */
    public function showFilters(array $data): array
    {
        $type = $data['type'];
        $appId = $data['app_id'];
        $app = App::find($appId);

        $filterService = new FilterService;
        if(!empty($app)) $filterService->setApp($app);
        $filterResourceType = FilterResourceType::tryFrom($type);

        return $filterService->getFiltersByResourceType($filterResourceType);
    }
}
