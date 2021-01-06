<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function returnSuccess($massage, $data)
    {
        return response()->json([
            'code' => strval(200),
            'status' => true,
            'massage' => $massage,
            'data' => $data,
        ], 200);
    }

    public function returnError($massage, $code)
    {
        return response()->json([
            'code' => strval($code),
            'status' => false,
            'massage' => $massage,
            'data' => [],
        ], 200);
    }
}
