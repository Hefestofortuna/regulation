<?php

namespace App\Http\Controllers;

use App\Repositories\SpecializationRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class SpecializationController extends ApiController
{
    public function __construct(
        private SpecializationRepository $specializationRepository
    ) {

    }
    public function user(): Response
    {
        $result = $this->specializationRepository->findUserSpecializationFromPred(Auth::user()->pred_id);
        return $this->successResponse($result);
    }
    public function brigade(Request $request): Response
    {
        $this->validate($request, ['pred_id' => 'required|integer']);
        $result = $this->specializationRepository->findBrigadeFromPred($request->input("pred_id"));
        return $this->successResponse($result);
    }

}
