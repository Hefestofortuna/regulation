<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DevicesController extends Controller
{
    public function getDevices(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json(DB::select("
            select
            lpp.dist_id as pred_id, lpp.obj_osn_id
              ,sum(case when lpp.next_zam_date < date_trunc('month',now())    then 1 else 0  end )  as expired_appliances
              ,sum(case when date_trunc('month', lpp.next_zam_date) = date_trunc('month',now())    then 1 else 0  end ) as monthly_replacement_plan

              ,max(fct.cnt) as of_which_checked_in_rtu
              ,max(rc.cnt_checked_rtu) as in_rtu
              ,max(rc.cnt_checked_ooi) as in_station
              ,max(rfe.cnt)

            from  dbo.l_place2prib lpp
            left outer join dbo.place plc on plc.dist_id = lpp.pred_id and plc.obj_osn_id  = lpp.obj_osn_id and plc.obj_id  = lpp.obj_id and plc.mesto_id  = lpp.mesto_id and plc.mesto_id >0

            left outer join
            (
                select * from dbo.rtu_checked rc
                where
                record_num = (SELECT max(record_num)
                FROM dbo.rtu_checked rc2
                WHERE
                rc.pred_id = rc2.pred_id and rc.obj_osn_id = rc2.obj_osn_id)
            ) as rc
            on rc.pred_id = lpp.pred_id and rc.obj_osn_id = lpp.obj_osn_id

            left outer join
            (
                select * from dbo.rtu_fact_exchng rfe
                where
                record_num = (SELECT max(record_num)
                FROM dbo.rtu_fact_exchng rfe2
                WHERE
                rfe.pred_id = rfe2.pred_id and rfe.obj_osn_id = rfe2.obj_osn_id)
            ) as rfe
            on rfe.pred_id = lpp.pred_id and rfe.obj_osn_id = lpp.obj_osn_id

            left outer join
            (
                select count(*) cnt, obj_osn_id, rp.pred_id  from dbo.rtu_schedule rs
                inner join dbo.rtu_plan rp on rp.plan_id = rs.plan_id
                inner join dbo.rtu_schedule_mark rsm on rs.schedule_id = rsm.schedule_id
                inner join dbo.rtu_schedule_place rsp  on rs.schedule_id = rsp.schedule_id
                inner join dbo.rtu_schedule_sotr rss  on rs.schedule_id = rss.schedule_id
                where mark_id is not null and schedule_status_id = 4
                and to_char(rs.date_begin::timestamp, 'YYYYMM') = (select to_char(now()::timestamp, 'YYYYMM'))
                group by obj_osn_id, rp.pred_id
            ) as fct
            on lpp.pred_id = fct.pred_id and fct.obj_osn_id=lpp.obj_osn_id

            where plc.mesto_id >0   and   lpp.pred_id = ?
            group by lpp.dist_id, lpp.obj_osn_id
        ", [
            $request->input("pred_id"),
        ]));
    }

    public function putDevices(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $json = (array)json_decode($request->getContent());

            $checked = null;
            $checked_rtu = null;
            $checked_ooi = null;
            $pred_id = null;
            $obj_osn_id = null;

            $asu_pred_id = Auth::user()->asu_pred_id;
            $asu_sotr_id = Auth::user()->asu_sotr_id;

            if ($asu_pred_id == null) {
                $asu_pred_id = 3025;
            }
            if ($asu_sotr_id == null) {
                $asu_sotr_id = 686;
            }

            foreach ($json as $item) {
                if ($item->type == "fact") {
                    $result = DB::selectOne(
                        "SELECT * FROM dbo.rtu_fact_exchng WHERE pred_id=? AND obj_osn_id=? ORDER BY record_num DESC LIMIT 1;",
                        [
                            $item->pred_id,
                            $item->obj_osn_id
                        ]
                    );

                    $resultText = "INSERT INTO dbo.rtu_fact_exchng(pred_id, obj_osn_id, record_num, cnt, date, record_date) VALUES (%s, %s, %s, %s, now(), %s)";
                    $resultText = sprintf($resultText, $item->pred_id, $item->obj_osn_id, (!empty($result)) ? ($result->record_num + 1) : 1, $item->value, date('Y-m-d H:i:s'));

                    DB::insert(
                        "INSERT INTO dbo.rtu_fact_exchng(pred_id, obj_osn_id, record_num, cnt, date, record_date) VALUES (?, ?, ?, ?, now(), ?);",
                        [
                            $item->pred_id,
                            $item->obj_osn_id,
                            (!empty($result)) ? ($result->record_num + 1) : 1,
                            $item->value,
                            date('Y-m-d H:i:s')
                        ]
                    );
                } elseif ($item->type == "checked" or $item->type == "rtu" or $item->type == "station") {
                    if ((($pred_id !== $item->pred_id or $obj_osn_id !== $item->obj_osn_id) and ($pred_id !== null and $obj_osn_id !== null))) {
                        $result = DB::selectOne(
                            "SELECT * FROM dbo.rtu_checked WHERE pred_id=? AND obj_osn_id=? ORDER BY record_num DESC LIMIT 1;",
                            [
                                $pred_id,
                                $obj_osn_id
                            ]
                        );

                        if ($checked == null and !empty($result)) {
                            $checked = $result->cnt_checked;
                        }
                        if ($checked_rtu == null and !empty($result)) {
                            $checked_rtu = $result->cnt_checked_rtu;
                        }
                        if ($checked_ooi == null and !empty($result)) {
                            $checked_ooi = $result->cnt_checked_ooi;
                        }

                        DB::insert(
                            "INSERT INTO dbo.rtu_checked(pred_id, obj_osn_id, record_num, cnt_checked, cnt_checked_rtu, cnt_checked_ooi, date, record_date, predsotr_id, sort_id)
                                VALUES (?, ?, ?, ?, ?, ?, now(), ?, ?, ?);",
                            [
                                $pred_id,
                                $obj_osn_id,
                                (!empty($result)) ? ($result->record_num + 1) : 1,
                                $checked,
                                $checked_rtu,
                                $checked_ooi,
                                date('Y-m-d H:i:s'),
                                $asu_pred_id,
                                $asu_sotr_id
                            ]
                        );
                        $checked = null;
                        $checked_rtu = null;
                        $checked_ooi = null;
                        $pred_id = null;
                        $obj_osn_id = null;
                    }
                    if ($item->type == "checked") {
                        $checked = $item->value;
                    }
                    if ($item->type == "rtu") {
                        $checked_rtu = $item->value;
                    }
                    if ($item->type == "station") {
                        $checked_ooi = $item->value;
                    }

                    $pred_id = $item->pred_id;
                    $obj_osn_id = $item->obj_osn_id;
                } else {
                    throw new Exception("Мы такое пока не обрабатываем", 0);
                }
            }

            if ($pred_id !== null and $obj_osn_id !== null) {
                $result = DB::selectOne(
                    "SELECT * FROM dbo.rtu_checked WHERE pred_id=? AND obj_osn_id=? ORDER BY record_num DESC LIMIT 1;",
                    [
                        $pred_id,
                        $obj_osn_id
                    ]
                );

                if ($checked == null and !empty($result)) {
                    $checked = $result->cnt_checked;
                }
                if ($checked_rtu == null and !empty($result)) {
                    $checked_rtu = $result->cnt_checked_rtu;
                }
                if ($checked_ooi == null and !empty($result)) {
                    $checked_ooi = $result->cnt_checked_ooi;
                }

                DB::insert(
                    "INSERT INTO dbo.rtu_checked(pred_id, obj_osn_id, record_num, cnt_checked, cnt_checked_rtu, cnt_checked_ooi, date, record_date, predsotr_id, sort_id)
                                VALUES (?, ?, ?, ?, ?, ?, now(), ?, ?, ?);",
                    [
                        $pred_id,
                        $obj_osn_id,
                        (!empty($result)) ? ($result->record_num + 1) : 1,
                        $checked,
                        $checked_rtu,
                        $checked_ooi,
                        date('Y-m-d H:i:s'),
                        $asu_pred_id,
                        $asu_sotr_id
                    ]
                );
            }

            return response([
                'code' => 1,
                'message' => 'Данные успешно добавлены  ',
                'type' => 'success'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'type' => 'error'
            ], 500);
        }
    }
}
