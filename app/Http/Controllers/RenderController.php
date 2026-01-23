<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RenderController extends Controller
{
    /**
     * Render the application.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function render(Request $request)
    {
        $meta = [
            'type' => 'website',
            'title' => config('app.name'),
            'image' => url('/images/logo.png'),
            'url' => rtrim(config('app.url'), '/'),
            'description' => 'Welcome to '.config('app.name'),
        ];

        return view('render', compact('meta'));
    }
}
