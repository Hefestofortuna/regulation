<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class StationRepository implements Repository
{
    public function findAllForPred(int $predId): array
    {
        return DB::select(
            "
            select 
              asu_obj_dis.pred_id, 
              asu_obj_osn_inf.obj_osn_id, 
              asu_obj_osn_inf.name 
            from 
              dbo.asu_obj_osn_inf 
              left join dbo.asu_obj_dis on asu_obj_osn_inf.obj_osn_id = asu_obj_dis.obj_osn_id 
            where 
              asu_obj_dis.pred_id = ? 
            order by 
              asu_obj_osn_inf.name
",
            [
                $predId,
            ]
        );
    }
}
