<?php

namespace App\Http\Controllers;

use App\Models\DTO\TroubleshootingDTO;
use App\Models\DTO\TroubleshootingsDTO;
use App\Repositories\TroubleshootingRepository;
use App\Services\TroubleshootingService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class TroubleshootingController extends ApiController
{
    public function __construct(
        private TroubleshootingRepository $troubleshootingRepository,
        private TroubleshootingService    $troubleshootingService
    ) {
    }

    public function update(Request $request): JsonResponse
    {
        $this->validate($request, [
            'pred_id' => 'required|integer',
            'podr_id' => 'required|integer',
            'obj_osn_id' => 'required|integer',
            'type' => 'required|string',
        ]);
        $DTO = TroubleshootingsDTO::fromRequest($request);
        $this->troubleshootingService->processReglaments(
            $DTO
        );

        return $this->successResponse([]);
    }

    public function list(Request $request): JsonResponse
    {
        $this->validate($request, [
            'pred_id' => 'required|integer',
            'podr_id' => 'required|integer',
        ]);
        $DTO = TroubleshootingDTO::fromRequest($request);
        $result = $this->troubleshootingRepository->findAllForTroubleshooting($DTO);
        return $this->successResponse($result);
    }

    public function history(Request $request): Response
    {
        $this->validate($request, [
            'pred_id' => 'required|integer',
            'podr_id' => 'required|integer',
            'obj_osn_id' => 'required|integer',
        ]);
        $DTO = TroubleshootingDTO::fromRequest($request);
        $result = $this->troubleshootingRepository->findHistoryForTroubleshooting($DTO);
        return $this->successResponse($result);
    }
}
