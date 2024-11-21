<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class StationController extends Controller
{
    public function list(Request $request): JsonResponse
    {
        $result = DB::select(
            "select asu_obj_dis.pred_id, asu_obj_osn_inf.obj_osn_id, asu_obj_osn_inf.name from dbo.asu_obj_osn_inf left join dbo.asu_obj_dis on asu_obj_osn_inf.obj_osn_id = asu_obj_dis.obj_osn_id where asu_obj_dis.pred_id=? order by asu_obj_osn_inf.name",
            [
                $request->input("pred_id")
            ]
        );
        return response()->json($result);
    }

}
