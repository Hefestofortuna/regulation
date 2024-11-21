<?php

namespace App\Models\DTO;

use Illuminate\Http\Request;

class TroubleshootingDTO implements DTOFromRequest
{
    public function __construct(
        public ?int $predId,
        public ?int $podrId,
        public ?int $objOsnId,
        public ?string $type,
    ) {
    }

    public static function fromRequest(Request $request): TroubleshootingDTO
    {
        return new self(
            $request->input('pred_id'),
            $request->input('podr_id'),
            $request->input('obj_osn_id'),
            $request->input('type'),
        );
    }
}
