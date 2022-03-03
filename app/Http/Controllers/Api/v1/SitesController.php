<?php

namespace App\Http\Controllers\Api\v1;

use App\Site;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SitesController extends Controller
{
    public function index(Request $request)
    {
        return Site::all();
    }

    public function update(Request $request)
    {

    }
}
