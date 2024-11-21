<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class DeliveryRepository implements Repository
{
    public function findAllFromObjOsnAndTrvType(int $objOsnIdA, int $trvType)
    {
        return DB::select(
            "SELECT *
                    FROM   dbo.mtrx_trvtime
                    WHERE  finish_date != '9999-12-12 00:00:00'
                           AND obj_osn_id_a =?
                           AND trvtype =?
                    ORDER  BY start_date DESC
                    LIMIT  5",
            [
                $objOsnIdA,
                $trvType
            ]
        );
    }
}
