<?php

namespace App\Http\Controllers;

use App\Repositories\DeviceRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class DevicesController extends ApiController
{
    public function __construct(
        private DeviceRepository $deviceRepository,
    ) {
    }
    public function list(Request $request)
    {
        $response = $this->deviceRepository->findAllForPred($request->input("pred_id"));
        return $this->successResponse($response);
    }

    public function update(Request $request): JsonResponse
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
