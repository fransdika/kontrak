<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class api_m extends Model
{
    use HasFactory;

    public function get_list_verified_customer($comp_id)
    {
        return DB::select("SELECT misterkong_$comp_id.m_customer.kd_customer,
                            company_id 
                            FROM m_user_company mp_company 
                            INNER JOIN misterkong_$comp_id.m_customer m_customer ON m_customer.email=mp_company.email_usaha");
    }
    
    public function get_list_customer_contract($comp_id)
    {
        return DB::select("SELECT
        m_customer.kd_customer,
        m_customer.nama,
        m_customer.alamat,
        m_customer.telepon,
        fax,
        kontak,
        m_customer.hp,
        m_customer.email,
        mp_company.id,
        mp_company.company_id,
        mp_company.db_name,
        IFNULL( m_customer_config.`status`, 0 ) AS `status`,
        IFNULL( m_customer_config.kontrak_id,- 1 ) AS kontrak_id,
        IFNULL( m_customer_config.id, 0 ) AS c_config_id,
        db_config_id_cid.this_id_cid AS this_id_cid,
        db_config_cid.this_cid AS this_cid 
        FROM
            misterkong_$comp_id.m_customer m_customer
            INNER JOIN misterkong_mp.m_user_company mp_company ON m_customer.email = mp_company.email_usaha
            INNER JOIN misterkong_$comp_id.m_customer_config m_customer_config ON m_customer_config.customer_user_company_id = mp_company.id
            INNER JOIN ( SELECT VALUE AS this_id_cid FROM misterkong_$comp_id.g_db_config g_db_config WHERE NAME = 'id_company_id' ) db_config_id_cid
            INNER JOIN ( SELECT VALUE AS this_cid FROM misterkong_$comp_id.g_db_config g_db_config WHERE NAME = 'company_id' ) db_config_cid");
    }
    
    
    public function get_list_supplier_contract($comp_id)
    {
        return DB::select("SELECT
                                * 
                            FROM
                                ( SELECT VALUE AS this_company_id FROM misterkong_$comp_id.g_db_config g_db_config WHERE NAME = 'company_id' ) me_data
                                INNER JOIN (
                                SELECT
                                    h_kontrak_request.comp_id_sumber,
                                    h_kontrak_request.comp_id_tujuan,
                                    h_kontrak_request.kd_customer,
                                    h_kontrak_request.kd_supplier,
                                    h_kontrak_request.periode_bulan 
                                FROM
                                    h_kontrak_request 
                                WHERE
                                    `status` = 0 
                                ) request ON request.comp_id_tujuan = me_data.this_company_id
                                INNER JOIN (
                                SELECT
                                    company.company_id, company.nama_usaha, company.alamat, company.no_telepon, company.email_usaha,
                                    ( SELECT nama FROM misterkong_mp.m_kategori_usaha WHERE kd_kategori_usaha = company.kategori_usaha ) AS usaha,
                                    ( SELECT a.province FROM misterkong_mp.m_province a WHERE a.id = company.kd_provinsi ) AS provinsi 
                                FROM
                                    misterkong_mp.m_userx m_userx
                                INNER JOIN m_user_company company ON m_userx.id = company.user_id 
                                ) data_user ON data_user.company_id = request.comp_id_sumber");
    }

    public function get_list_supplier_response_contract($comp_id)
    {
        return DB::select("SELECT
                    * 
                FROM
                    (
                    SELECT
                        m_supplier.kd_supplier,
                        m_supplier.nama,
                        m_supplier.alamat,
                        m_supplier.telepon,
                        fax,
                        kontak,
                        m_supplier.hp,
                        m_supplier.email,
                        mp_company.id AS sup_id_cid,
                        mp_company.company_id AS sup_cid,
                        mp_company.db_name AS sup_db,
                        m_supplier_config.`status` AS status_kontrak,
                        m_supplier_config.kontrak_id AS kontrak_id,
                        m_supplier_config.id AS c_config_id,
                        db_config_id_cid.this_id_cid AS cust_id_cid,
                        db_config_cid.this_cid AS cust_cid,
                        '1' AS status_response,
                        periode_bulan,
                        tanggal_request,
                        kd_customer,
                        histori_kontrak.id AS histori_id 
                    FROM
                        misterkong_$comp_id.m_supplier m_supplier
                        INNER JOIN misterkong_$comp_id.m_supplier_config m_supplier_config ON m_supplier.kd_supplier = m_supplier_config.kd_supplier
                        INNER JOIN misterkong_mp.m_user_company mp_company ON mp_company.id = m_supplier_config.supplier_user_company_id
                        INNER JOIN ( SELECT VALUE AS this_id_cid FROM misterkong_$comp_id.g_db_config g_db_config WHERE NAME = 'id_company_id' ) db_config_id_cid
                        INNER JOIN ( SELECT VALUE AS this_cid FROM misterkong_$comp_id.g_db_config g_db_config WHERE NAME = 'company_id' ) db_config_cid
                        INNER JOIN ( SELECT * FROM misterkong_mp.h_kontrak_request ) histori_kontrak ON histori_kontrak.comp_id_sumber = mp_company.company_id 
                        AND histori_kontrak.comp_id_tujuan = db_config_cid.this_cid 
                        AND histori_kontrak.kd_supplier = m_supplier_config.kd_supplier UNION
                    SELECT
                        m_supplier.kd_supplier,
                        m_supplier.nama,
                        m_supplier.alamat,
                        m_supplier.telepon,
                        fax,
                        kontak,
                        m_supplier.hp,
                        m_supplier.email,
                        ',' AS sup_id_cid,
                        ',' AS sup_cid,
                        ',' AS sup_db,
                        ',' AS status_kontrak,
                        ',' AS kontrak_id,
                        ',' AS c_config_id,
                        ',' AS cust_id_cid,
                        ',' AS cust_cid,
                        '0' AS status_response,
                        '-',
                        '-',
                        '-',
                        '-' 
                    FROM
                        misterkong_$comp_id.m_supplier m_supplier 
                    ) respon_kontrak 
                ORDER BY
                    status_response DESC");
    }

    public function get_Ready_for_pay($id, $cid_sumber, $cid_tujuan) 
    {
        return DB::select("SELECT
                                * 
                            FROM
                                ( SELECT id AS id_kontrak, no_kontrak, `status`, user_company_x_id, user_company_y_id FROM misterkong_mp.t_kontrak WHERE id = '$id' ) kontrak_selected
                                INNER JOIN ( SELECT kd_customer, customer_user_company_id, `status` AS status_customer_config, kontrak_id FROM misterkong_$cid_sumber.m_customer_config ) m_customer_config ON kontrak_selected.user_company_y_id = m_customer_config.customer_user_company_id
                                INNER JOIN (
                                    SELECT-- 		id,
                                    comp_id_sumber,
                                    comp_id_tujuan,
                                    kd_customer,
                                    kd_supplier,
                                    `status` AS status_history,
                                    DATE( tanggal_request ) AS tanggal_request,
                                    DATE( tanggal_response ) AS tanggal_response,
                                    DATE( tanggal_kontrak ) AS tanggal_kontrak,
                                    periode_bulan,
                                    DATE( tanggal_jatuh_tempo ) AS tanggal_jatuh_tempo 
                                FROM
                                    misterkong_mp.h_kontrak_request 
                                WHERE
                                    STATUS <> 1 
                                    AND comp_id_sumber = '$cid_sumber' 
                                AND comp_id_tujuan = '$cid_tujuan' 
                                ) histori ON histori.kd_customer = m_customer_config.kd_customer");
    }   

}