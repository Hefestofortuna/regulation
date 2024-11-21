<?php

namespace App\Services;

use App\Models\DTO\TroubleshootingDTO;
use App\Models\DTO\TroubleshootingsDTO;
use App\Models\Enums\ObjectType;
use App\Repositories\TroubleshootingRepository;

class TroubleshootingService
{
    public function __construct(
        private TroubleshootingRepository $troubleshootingRepository
    ) {
    }

    public function processReglaments(TroubleshootingsDTO $arrayOfDTOs): void
    {
        /** @var TroubleshootingDTO $item */
        foreach ($arrayOfDTOs as $item) {
            $item->type = ObjectType::fromString($item->type);

            $existingReglament = $this->troubleshootingRepository->findLatestReglament(
                $item->pred_id,
                $item->podr_id,
                $item->obj_osn_id,
                $item->type
            );

            if (!empty($existingReglament)) {
                $this->troubleshootingRepository->updateReglament($existingReglament, $item->value);
            } else {
                $this->troubleshootingRepository->insertReglament($item, 1);
            }
        }
    }
}
