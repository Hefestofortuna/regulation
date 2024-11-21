<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class DeviceRepository implements Repository
{
    public function findAllForPred(int $predId): array
    {
        return DB::select(
            "select
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
            group by lpp.dist_id, lpp.obj_osn_id",
            [
                $predId,
            ]
        );
    }
}
