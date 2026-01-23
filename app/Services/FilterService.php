<?php

namespace App\Services;

use App\Models\App;
use App\Enums\FilterResourceType;
use Illuminate\Support\Facades\DB;

class FilterService
{
    private $app;

    /**
     * Set app.
     *
     * @param App $app
     * @return self
     */
    public function setApp(App $app): self
    {
        $this->app = $app;
        return $this;
    }

    /**
     * Get filters for a specific resource type.
     *
     * @param FilterResourceType $filterResourceType
     * @return array
     */
    public function getFiltersByResourceType(FilterResourceType $filterResourceType): array
    {
        switch ($filterResourceType) {
            case FilterResourceType::USSD_SESSIONS:
                return self::getUssdSessionFilters();
            case FilterResourceType::USSD_ACCOUNTS:
                return self::getUssdAccountFilters();
            default:
                return [];
        }
    }

    /**
     * Get filters for USSD sessions.
     *
     * @return array
     */
    private function getUssdSessionFilters(): array
    {
        $appId = $this->app->id;

        //  Unique country codes
        $countries = DB::table('ussd_sessions')
            ->where('app_id', $appId)->whereNotNull('country')->distinct()
            ->pluck('country')->sort()->values()->toArray();

        //  Unique network names
        $networks = DB::table('ussd_sessions')
            ->where('app_id', $appId)->whereNotNull('network')->distinct()
            ->pluck('network')->sort()->values()->toArray();

        return [
            'countries' => $countries,
            'networks'  => $networks
        ];
    }

    /**
     * Get filters for USSD accounts.
     *
     * @return array
     */
    private function getUssdAccountFilters(): array
    {
        $appId = $this->app->id;

        //  Unique country codes
        $countries = DB::table('ussd_accounts')
            ->where('app_id', $appId)->whereNotNull('country')->distinct()
            ->pluck('country')->sort()->values()->toArray();

        //  Unique network names
        $networks = DB::table('ussd_accounts')
            ->where('app_id', $appId)->whereNotNull('network')->distinct()
            ->pluck('network')->sort()->values()->toArray();

        return [
            'countries' => $countries,
            'networks'  => $networks
        ];
    }
}
