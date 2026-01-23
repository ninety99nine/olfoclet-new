<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class AppUserResources extends ResourceCollection
{
    public $collects = AppUserResource::class;
}
