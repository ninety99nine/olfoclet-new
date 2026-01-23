<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class AppResources extends ResourceCollection
{
    public $collects = AppResource::class;
}
