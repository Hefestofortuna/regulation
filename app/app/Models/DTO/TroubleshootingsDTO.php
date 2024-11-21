<?php

namespace App\Models\DTO;

use Illuminate\Http\Request;

class TroubleshootingsDTO implements DTOFromRequest
{
    public function __construct(
        /* @var $troubleshooting TroubleshootingDTO */
        public ?array $troubleshooting,
    ) {
    }

    public static function fromRequest(Request $request): TroubleshootingsDTO
    {

        $arrayFromRequest = (array) json_decode($request->getContent());
        $arrayOfDTOs = [];
        foreach ($arrayFromRequest as $item) {
            $arrayOfDTOs[] = new TroubleshootingDTO(
                predId: $item['predId'],
                podrId: $item['predId'],
                objOsnId: $item['predId'],
                type: $item['predId'],
            );
        }
        return new self($arrayOfDTOs);
    }
}
