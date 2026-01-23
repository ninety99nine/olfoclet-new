<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BaseResource extends JsonResource
{
    /**
     * Determine qualitative status of average wait time
     *
     * @param int $milliseconds
     * @return string "low" | "medium" | "high" | "unknown"
     */
    protected function getDurationStatus(?float $milliseconds): string
    {
        $seconds = $milliseconds / 1000;

        if ($seconds <= 5) {
            return 'low';
        }

        if ($seconds <= 15) {
            return 'medium';
        }

        return 'high';
    }
}
