<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PlatformController extends Controller
{
    public function index()
    {
        return \App\Models\Platform::all();
    }
}
