<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class SpecializationRepository implements Repository
{
    public function findUserSpecializationFromPred(int $predId): array
    {
        return DB::select("
            SELECT dor.sname               AS dor_name,
                dor.dor_kod,
                pred.sname              AS pred_name,
                pred.pred_id,
                CASE
                    WHEN Length(Trim(podr.sname)) = 0 THEN Trim(podr.NAME)
                    ELSE Trim(podr.sname)
                END                     AS podr_name,
                podr.podr_id,
                COALESCE(pers.NAME
                            || ' ', 'Не указано')
                || COALESCE(Substring(pers.first_name, 1, 1)
                            || '.', '')
                || COALESCE(Substring(pers.last_name, 1, 1)
                            || '.', '') AS personal_name,
                pers.sotr_id
            FROM   dbo.asu_personal pers
                LEFT OUTER JOIN dbo.asu_pred pred
                                ON pred.pred_id = pers.pred_id
                LEFT OUTER JOIN dbo.asu_dor dor
                                ON dor.dor_kod = pred.dor_kod
                LEFT OUTER JOIN dbo.asu_podr podr
                                ON podr.pred_id = pers.pred_id
                                AND podr.podr_id = pers.podr_id
                LEFT OUTER JOIN dbo.asu_podr_viddej apv
                                ON podr.pred_id = apv.pred_id
                                AND podr.podr_id = apv.podr_id
            WHERE  pers.cor_tip NOT IN ( 'D', 'd' )
                AND pred.gr_id = 70
                AND podr.cor_tip NOT IN ( 'D' )
                AND apv.vd_id = 903
                AND pers.cor_tip NOT IN ( 'D' )
                AND pred.pred_id = ?
            ", [$predId]);
    }

    public function findBrigadeFromPred(int $predId): array
    {
        return DB::select("
            SELECT Cast(apo.pred_id AS VARCHAR)
                   || ':'
                   || Cast(apo.podr_id AS VARCHAR) AS PRED_PODR,
                   apo.sname,
                   apo.vname
            FROM   dbo.asu_podr apo
                   INNER JOIN dbo.asu_pred apr
                           ON apo.pred_id = apr.pred_id
                   INNER JOIN dbo.asu_podr_viddej apv
                           ON apo.pred_id = apv.pred_id
                              AND apo.podr_id = apv.podr_id
            WHERE  apo.pred_id = ?
                   AND apr.gr_id = 70
                   AND apo.cor_tip NOT IN ( 'D', 'd' )
                   AND apv.vd_id = 903
                   AND apo.tpodr_id > 1
        ", [
            $predId
        ]);
    }
}
