<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class api_m extends Model
{
    use HasFactory;
    public function get_selected_contract($id_kontrak,$cid_sumber,$cid_tujuan)
    {
        return DB::select("SELECT
                                kontrak_selected.id_kontrak, histori.tanggal_request, histori.tanggal_response, histori.tanggal_kontrak, histori.periode_bulan, histori.tanggal_jatuh_tempo
                            FROM
                                ( SELECT id AS id_kontrak,user_company_y_id FROM misterkong_mp.t_kontrak WHERE id = '$id_kontrak' ) kontrak_selected
                                INNER JOIN (SELECT kd_customer, kontrak_id, customer_user_company_id FROM	misterkong_$cid_sumber.m_customer_config) m_customer_config ON kontrak_selected.user_company_y_id = m_customer_config.customer_user_company_id
                                INNER JOIN (
                                SELECT
                                    id,
                                    kd_customer,
                                    `status`,
                                    DATE( tanggal_request ) AS tanggal_request,
                                    DATE( tanggal_response ) AS tanggal_response,
                                    DATE( tanggal_kontrak ) AS tanggal_kontrak,
                                    periode_bulan,
                                    DATE( tanggal_jatuh_tempo ) AS tanggal_jatuh_tempo 
                                FROM
                                    misterkong_mp.h_kontrak_request 
                                WHERE
                                    `status` <> 1 
                                    AND comp_id_sumber = '$cid_sumber' 
                                AND comp_id_tujuan = '$cid_tujuan' 
                                ) histori ON histori.kd_customer = m_customer_config.kd_customer");
    }

}