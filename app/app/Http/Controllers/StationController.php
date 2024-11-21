<?php

namespace App\Http\Controllers;

use App\Repositories\StationRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StationController extends ApiController
{
    public function __construct(
        private StationRepository $stationRepository
    ) {
    }
    public function list(Request $request): Response
    {
        $this->validate($request, ['pred_id' => 'required|integer']);
        $result = $this->stationRepository->findAllForPred($request->input("pred_id"));
        return $this->successResponse($result);
    }

}
