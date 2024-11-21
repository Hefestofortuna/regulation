<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class DeliveryController extends Controller
{
    public function getHistory(Request $request): JsonResponse
    {
        (object)json_decode($request->getContent());
        $result = DB::select(
            "select * from dbo.mtrx_trvtime where finish_date != '9999-12-12 00:00:00' and obj_osn_id_a=? and trvtype=? order by start_date desc limit 5",
            [
                $request->input("obj_osn_id_a"),
                $request->input("trvtype")
            ]
        );
        return response()->json($result);
    }

    public function getTimes(Request $request): JsonResponse
    {
        DB::statement("drop table if exists vars;");
        DB::statement("create temp table vars (pred_id int not null, dt timestamp not null,type int not null);");
        DB::insert("insert into vars(pred_id,dt,type) values(?,'2021-12-20',unnest(array[1,2,3,4]));", [$request->input("pred_id")]);
        $result = DB::selectOne("
select
	json_object(
		array_agg(
		   array[coalesce(mtrx.pred_id, nn.pred_id) ||  '_'  || coalesce(mtrx.obj_osn_id_a, nn.obj_osn_id_a) || '_' ||  coalesce(mtrx.obj_osn_id_b, nn.obj_osn_id_b) || '_' ||  mtrx.trvtype, mtrx.trvtime::text]
		)
)
from
  dbo.mtrx_trvtime mtrx full
  outer join (
    select
      od1.pred_id,
      od1.obj_osn_id as obj_osn_id_a,
      od2.obj_osn_id as obj_osn_id_b,
      'ok' as stat,
      vars.type
    from
      dbo.asu_obj_dis od1
      inner join dbo.asu_obj_dis od2 on od1.pred_id = od2.pred_id
      inner join vars on vars.pred_id = od1.pred_id
    where
      od1.obj_osn_id <= od2.obj_osn_id
  ) as nn on mtrx.pred_id = nn.pred_id
  and mtrx.obj_osn_id_a = nn.obj_osn_id_a
  and mtrx.obj_osn_id_b = nn.obj_osn_id_b
  and mtrx.trvtype = nn.type full
  outer join vars on mtrx.pred_id = vars.pred_id
  and mtrx.trvtype = vars.type
  and mtrx.finish_date > now()
where

  mtrx.trvtype is not null
  and date_part(
    'year',
    coalesce(vars.dt, '2030-01-01')
  ) <= date_part(
    'year',
    coalesce(mtrx.finish_date, '2099-01-01')
      )
  ")->json_object;
        return response()->json(json_decode($result));
    }

    public function putTimes(Request $request): JsonResponse
    {
        $json = (array)json_decode($request->getContent());
        foreach ($json as $item) {
            $result = DB::select(
                "select * from dbo.mtrx_trvtime where trvtype=? and obj_osn_id_a=? and obj_osn_id_b=? order by record_num desc limit 1;",
                [
                    $item->trvtype,
                    $item->obj_osn_id_a,
                    $item->obj_osn_id_b
                ]
            );
            if (!empty($result)) {
                $result = end($result);
                if (($result->finish_date > date('Y-m-d H:i:s')) && ($result->trvtime != $item->trvtime)) {
                    DB::update(
                        "UPDATE dbo.mtrx_trvtime SET finish_date=now() WHERE (pred_id = ? and obj_osn_id_a = ? and obj_osn_id_b = ? and record_num = ? and trvtype=?);",
                        [
                            $result->pred_id,
                            $result->obj_osn_id_a,
                            $result->obj_osn_id_b,
                            $result->record_num,
                            $result->trvtype
                        ]
                    );
                    DB::insert(
                        "INSERT INTO dbo.mtrx_trvtime(pred_id, obj_osn_id_a, obj_osn_id_b, record_num, trvtype, trvtime, start_date, finish_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?);",
                        [
                            $item->pred_id,
                            $item->obj_osn_id_a,
                            $item->obj_osn_id_b,
                            $result->record_num + 1,
                            $item->trvtype,
                            $item->trvtime,
                            date('Y-m-d H:i:s'), '9999-12-12 00:00:00'
                        ]
                    );
                }
            } else {
                DB::insert(
                    "INSERT INTO dbo.mtrx_trvtime(pred_id, obj_osn_id_a, obj_osn_id_b, record_num, trvtype, trvtime, start_date, finish_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?);",
                    [
                        $item->pred_id,
                        $item->obj_osn_id_a,
                        $item->obj_osn_id_b,
                        1,
                        $item->trvtype,
                        $item->trvtime,
                        date('Y-m-d H:i:s'),
                        '9999-12-12 00:00:00'
                    ]
                );
            }

        }
        return response()->json([], 200);
    }
}
