<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class DeploymentResources extends ResourceCollection
{
    public $collects = DeploymentResource::class;
}
