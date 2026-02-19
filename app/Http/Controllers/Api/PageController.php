<?php

namespace App\Http\Controllers\Api;

use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function show(string $slug)
    {
        $page = Page::where('slug', $slug)->firstOrFail();


        return response()->json($page);
    }
}
