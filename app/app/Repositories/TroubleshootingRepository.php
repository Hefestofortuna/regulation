<?php

namespace App\Repositories;

use App\Models\DTO\TroubleshootingDTO;
use Illuminate\Support\Facades\DB;

class TroubleshootingRepository implements Repository
{
    public function findAllForTroubleshooting(TroubleshootingDTO $troubleshootingDTO): array
    {
        return DB::select(
            "
        SELECT
            ope.pred_id,
            NULLIF(regexp_replace(asu_podr.sname, '\D','','g'), '')::numeric AS sort,
            ope.podr_id,
            ope.obj_osn_id,
            asu_podr.sname,
            asu_obj_osn_inf.NAME,
            COALESCE(Max(CASE
                           WHEN mtrx.reglamenttype = 3207 THEN reglamenttime
                           ELSE NULL
                         END), 0) AS swtch,
            COALESCE(Max(CASE
                           WHEN mtrx.reglamenttype = 3209 THEN reglamenttime
                           ELSE NULL
                         END), 0) AS rc,
            COALESCE(Max(CASE
                           WHEN mtrx.reglamenttype = 3211 THEN reglamenttime
                           ELSE NULL
                         END), 0) AS pwrspl,
            COALESCE(Max(CASE
                           WHEN mtrx.reglamenttype = 3213 THEN reglamenttime
                           ELSE NULL
                           END), 0) AS sgnl,
            COALESCE(Max(CASE
                           WHEN mtrx.reglamenttype = 3215 THEN reglamenttime
                           ELSE NULL
                         END), 0) AS cbl,
            COALESCE(Max(CASE
                           WHEN mtrx.reglamenttype = 3217 THEN reglamenttime
                           ELSE NULL
                         END), 0) AS crss,
            COALESCE(Max(CASE
                           WHEN mtrx.reglamenttype = 3219 THEN reglamenttime
                           ELSE NULL
                         END), 0) AS opcentr,
            COALESCE(Max(CASE
                           WHEN mtrx.reglamenttype = 3221 THEN reglamenttime
                           ELSE NULL
                         END), 0) AS ktsm,
            COALESCE(Max(CASE
                           WHEN mtrx.reglamenttype = 3223 THEN reglamenttime
                           ELSE NULL
                         END), 0) AS uksps
            FROM   dbo.asu_ob_podr_ex ope
                   LEFT OUTER JOIN dbo.mtrx_reglament mtrx
                                ON ope.pred_id = mtrx.pred_id
                                   AND ope.podr_id = mtrx.podr_id
                                   AND ope.obj_osn_id = mtrx.obj_osn_id
                   LEFT JOIN (SELECT pred_id,
                                           podr_id,
                                           obj_osn_id,
                                           reglamenttype,
                                           Max(record_num) AS record_num
                                    FROM   dbo.mtrx_reglament
                                    WHERE  pred_id = ?
                                    GROUP  BY pred_id,
                                              podr_id,
                                              obj_osn_id,
                                              reglamenttype) AS regmax
                                ON regmax.pred_id = mtrx.pred_id
                                   AND regmax.podr_id = mtrx.podr_id
                                   AND regmax.obj_osn_id = mtrx.obj_osn_id
    and regmax.reglamenttype=mtrx.reglamenttype
    AND regmax.record_num = mtrx.record_num
                   LEFT OUTER JOIN dbo.asu_podr
                                ON ope.podr_id = asu_podr.podr_id
                                   AND ope.pred_id = asu_podr.pred_id
                   LEFT JOIN dbo.asu_obj_osn_inf
                          ON ope.obj_osn_id = asu_obj_osn_inf.obj_osn_id
            WHERE  asu_obj_osn_inf.cor_tip NOT IN ( 'D', 'd', 'K', 'k' )
                   AND asu_podr.cor_tip NOT IN ( 'D', 'd', 'K', 'k' )
                   AND ope.pred_id = ?
            GROUP  BY ope.pred_id,
                      ope.podr_id,
                      ope.obj_osn_id,
                      asu_podr.sname,
                      asu_obj_osn_inf.NAME
            ORDER BY sort
    ",
            [
                $troubleshootingDTO->podrId,
                $troubleshootingDTO->predId,
            ]
        );
    }

    public function findHistoryForTroubleshooting(TroubleshootingDTO $troubleshootingDTO): array
    {
        return DB::select("
              SELECT
          pred_id,
          podr_id,
          obj_osn_id,
          sum(mtrx.swtch) as swtch,
          sum(mtrx.rc) as rc,
          sum(mtrx.cbl) as cbl,
          sum(mtrx.pwrspl) as pwrspl,
          sum(mtrx.sgnl) as sgnl,
          sum(mtrx.crss) as crss,
          sum(mtrx.opcentr) as opcentr,
          sum(mtrx.ktsm) as ktsm,
          sum(mtrx.uksps) as uksps,
          finish_date
        FROM
          (
            SELECT
              mtrx_reglament.pred_id,
              mtrx_reglament.podr_id,
              mtrx_reglament.obj_osn_id,
              CASE WHEN mtrx_reglament.reglamenttype = 3207 THEN reglamenttime ELSE NULL END AS swtch,
              CASE WHEN mtrx_reglament.reglamenttype = 3209 THEN reglamenttime ELSE NULL END AS rc,
              CASE WHEN mtrx_reglament.reglamenttype = 3211 THEN reglamenttime ELSE NULL END AS pwrspl,
              CASE WHEN mtrx_reglament.reglamenttype = 3213 THEN reglamenttime ELSE NULL END AS sgnl,
              CASE WHEN mtrx_reglament.reglamenttype = 3215 THEN reglamenttime ELSE NULL END AS cbl,
              CASE WHEN mtrx_reglament.reglamenttype = 3217 THEN reglamenttime ELSE NULL END AS crss,
              CASE WHEN mtrx_reglament.reglamenttype = 3219 THEN reglamenttime ELSE NULL END AS opcentr,
              CASE WHEN mtrx_reglament.reglamenttype = 3221 THEN reglamenttime ELSE NULL END AS ktsm,
              CASE WHEN mtrx_reglament.reglamenttype = 3223 THEN reglamenttime ELSE NULL END AS uksps,
              mtrx_reglament.finish_date
            FROM
              dbo.mtrx_reglament mtrx_reglament
            WHERE
              pred_id = ?
              AND podr_id = ?
              AND obj_osn_id = ?
              AND mtrx_reglament.finish_date < now()
              AND mtrx_reglament.finish_date in (
                SELECT
                  DISTINCT finish_date
                FROM
                  dbo.mtrx_reglament
                WHERE
                  mtrx_reglament.pred_id = ?
                  AND mtrx_reglament.podr_id = ?
                  AND mtrx_reglament.obj_osn_id = ?
                  AND finish_date < now()
                ORDER BY
                  finish_date DESC
                LIMIT
                  5
              )
            ORDER BY
              finish_date DESC
          ) AS mtrx
        group by
          pred_id,
          podr_id,
          obj_osn_id,
          finish_date
        ORDER BY
          finish_date ASC
       ", [
            $troubleshootingDTO->predId,
            $troubleshootingDTO->podrId,
            $troubleshootingDTO->objOsnId,
            $troubleshootingDTO->predId,
            $troubleshootingDTO->podrId,
            $troubleshootingDTO->objOsnId
        ]);
    }

    public function findLatestReglament(TroubleshootingDTO $troubleshootingDTO)
    {
        return DB::select(
            "select * from dbo.mtrx_reglament where pred_id=? and podr_id=? and obj_osn_id=? and reglamenttype=? order by record_num desc limit 1",
            [$troubleshootingDTO->predId, $troubleshootingDTO->podrId, $troubleshootingDTO->objOsnId, $troubleshootingDTO->type]
        );
    }

    public function updateReglament(array $existingReglament, string $newValue): void
    {
        DB::update(
            "UPDATE dbo.mtrx_reglament SET finish_date='" . date('Y-m-d H:i:s') . "' WHERE (pred_id=? and podr_id=? and obj_osn_id=? and reglamenttype=? and record_num=?);",
            [
                $existingReglament['pred_id'],
                $existingReglament['podr_id'],
                $existingReglament['obj_osn_id'],
                $existingReglament['reglamenttype'],
                $existingReglament['record_num']
            ]
        );

        DB::insert(
            "INSERT INTO dbo.mtrx_reglament(pred_id, podr_id, obj_osn_id, reglamenttype, record_num, reglamenttime, start_date, finish_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?);",
            [
                $existingReglament->pred_id,
                $existingReglament->podr_id,
                $existingReglament->obj_osn_id,
                $existingReglament->reglamenttype,
                $existingReglament->record_num + 1,
                $newValue,
                date('Y-m-d H:i:s'),
                '9999-12-12 00:00:00'
            ]
        );
    }

    public function insertReglament(TroubleshootingDTO $item, int $recordNum): void
    {
        DB::insert(
            "INSERT INTO dbo.mtrx_reglament(pred_id, podr_id, obj_osn_id, reglamenttype, record_num, reglamenttime, start_date, finish_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?);",
            [
                $item->predId,
                $item->podrId,
                $item->objOsnId,
                $item->type,
                $recordNum,
                $item->value,
                date('Y-m-d H:i:s'),
                '9999-12-12 00:00:00'
            ]
        );
    }
}
