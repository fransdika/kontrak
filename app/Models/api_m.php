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
		m_customer.kd_customer,m_customer.nama, m_customer.alamat,m_customer.telepon,fax,kontak,m_customer.hp,
		m_customer.email,mp_company.id,mp_company.company_id,mp_company.db_name,IFNULL(m_customer_config.`status`,0) as status,IFNULL(m_customer_config.kontrak_id
		,-1) as kontrak_id,IFNULL(m_customer_config.id,0) as c_config_id,db_config_id_cid.this_id_cid as this_id_cid,db_config_cid.this_cid AS this_cid
		FROM m_customer INNER JOIN misterkong_mp.m_user_company mp_company ON m_customer.email=mp_company.email_usaha
		INNER JOIN m_customer_config ON m_customer_config.customer_user_company_id=mp_company.id
		INNER JOIN (SELECT value AS this_id_cid FROM g_db_config WHERE name='id_company_id') db_config_id_cid
		INNER JOIN (SELECT value AS this_cid FROM g_db_config WHERE name='company_id') db_config_cid");
    }
    

}