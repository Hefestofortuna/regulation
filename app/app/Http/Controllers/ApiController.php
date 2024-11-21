<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Lumen\Routing\Controller;

abstract class ApiController extends Controller
{
    protected function successResponse(array $payload, ?int $status = null): Response|JsonResponse
    {
        return response()->json($payload, $status ?: Response::HTTP_OK);
    }

    protected function arrayFromRequest(Request $request): array
    {
        return (array) json_decode($request->getContent());
    }
}
