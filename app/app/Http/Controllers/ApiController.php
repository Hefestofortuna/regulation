<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Laravel\Lumen\Routing\Controller;

abstract class ApiController extends Controller
{
    protected function successResponse(array $payload): Response|JsonResponse
    {
        return response()->json($payload, Response::HTTP_OK);
    }
}
