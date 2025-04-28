<?php

namespace App\Http\Controllers;

use App\Services\ApiDataService;

class ApiDataController extends Controller
{
    public function showData()
    {
        return ApiDataService::getRows();
    }
}
