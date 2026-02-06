<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class VersionResources extends ResourceCollection
{
    public $collects = VersionResource::class;
}
