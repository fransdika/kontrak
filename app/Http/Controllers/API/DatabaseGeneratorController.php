<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DatabaseGeneratorModel;

class DatabaseGeneratorController extends Controller
{
    private $table_list;
    private $trigger_list;
    private $vfp_list;
    private $default_val;
    private $query;
    private $dbm;

    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
        // $this->middleware('auth:api');
        $this->dbm = new DatabaseGeneratorModel();
    }

    public function getViewFunctionProcedure()
    {
        $data = file_get_contents(base_path('public/sync/procedure_function_view.txt'));
        $vfp_list = explode('xyv', $data);
        $this->vfp_list = $vfp_list;
    }
    public function getTable()
    {
        $table_list = $this->dbm->getTableList();
        $this->table_list = $table_list;
    }
    public function getTrigger()
    {
        $trigger_list = $this->dbm->getTriggerList();
        $this->trigger_list = $trigger_list;
    }
    public function getDefaultValue($request)
    {
        $content = file_get_contents(base_path('public/sync/default_value_pos.sql'));
		$def_value=explode(";", $content);
        // print_r($info_profile);
        $i = 5;
        foreach ($request->info_profile as $key => $value) {
            $query_info_profile[] = "('$i','$key','" . str_replace("'", "''", $value) . "')";
            $i++;
        }
        $query_insert_val[] = "INSERT INTO g_db_config(id,name,value) VALUES('1','company_id','" . $request->company_id . "'),('2','db_name','" . $request->db_name . "'),('3','id_company_id','" . $request->id_company_id . "'),('4','status_toko','1')";
        $query_insert_val[] = "INSERT INTO g_db_config VALUES " . implode(',', $query_info_profile);
        $query_insert_val[] = "INSERT INTO m_user_group(kd_group,nama,status) VALUES('GAA000','MANAGER','1'),('GAA001','KASIR','1')";
        $query_insert_val[] = "INSERT INTO m_userx(kd_user,kd_group,nama,passwd,status,m_UserLevels,passweb,keterangan) VALUES('UAA000','GAA000','" . str_replace("'", "''", $request->username) . "','','1','-1','" . str_replace("'", "''", $request->pwd) . "','')";
        $query_insert_val[] = "INSERT INTO m_pegawai(kd_pegawai,kd_jabatan,kd_jenis,kd_kota,kd_agama,kd_shift,kd_divisi,nama,tempat_lahir,tanggal_lahir,alamat,telepon,hp,ktp,tgl_masuk,kelamin,kelompok,point,keterangan,status_kawin,status,status_lembur,date_add,date_modif) VALUES('PAA000','JAA000','JAA000','KAA000','AAA000','JAA000','DAA000','PUSAT','-','1989-08-08 12:44:21','-','UAA000','" . $request->info_profile['hp'] . "','-','2007-01-01 12:44:21','0','0','0','-','1','2','1','2015-01-01 00:00:00','2015-01-01 00:00:00')";


        foreach ($def_value as $key_def => $value_def) {
            $query_insert_val[] = $value_def;
        }
        // echo "<pre>";
        // print_r($query_insert_val);
        // echo "</pre>";
        $this->default_val=$query_insert_val;
    }
    public function executeSql(Request $request)
    {

        // print_r($request->info_profile);
        $info_profile=$request->info_profile;

        $custom_query[]="CREATE DATABASE IF NOT EXISTS ".$request->db_name;
		$custom_query[]="USE ".$request->db_name;
		$custom_query[]="SET FOREIGN_KEY_CHECKS = 0";
        $this->getTable();
        $this->getTrigger();
        $this->getViewFunctionProcedure();
        $this->getDefaultValue($request);
        $query_all=array_merge($custom_query,$this->table_list,$this->trigger_list,$this->vfp_list,$this->default_val);

        $content_sql=str_replace("//;", "//", implode(";\n", $query_all));
		// $json_file=fopen("../../pr_multi_db/back_end_mp/db_def/solid_pos_".$request->company_id.".sql", "w+");//local
		$json_file=fopen("../../../public_html/back_end_mp/db_def/solid_pos_".$request->company_id.".sql", "w+");

		fclose($json_file);
		// $file_path = "../../pr_multi_db/back_end_mp/db_def/solid_pos_".$request->company_id.".sql";//local
		$file_path = "../../../public_html/back_end_mp/db_def/solid_pos_".$request->company_id.".sql"; //vps
		file_put_contents($file_path, $content_sql);


        $my_conmmand_file=fopen("../../../public_html/back_end_mp/db_def/mycommand".$request->company_id.".sh", "w+"); //vps
		fclose($my_conmmand_file);

		// $file_path = "../../back_end_mp/db_def/mycommand".$this->get_company_id().".sh"; //local
		$file_path = "../../../public_html/back_end_mp/db_def/mycommand".$request->company_id.".sh"; //vps

		file_put_contents($file_path, "cd /home/misterkong/public_html/back_end_mp/db_def\nmysql -h localhost -u admin_db -pWo%9TwbXcK@HSq9T < ./solid_pos_".$request->company_id.".sql");


		// shell_exec("chmod +x /home/misterkong/public_html/back_end_mp/db_def/mycommand".$request->company_id.".sh  > /dev/null &");
		// shell_exec("sh /home/misterkong/public_html/back_end_mp/db_def/mycommand".$request->company_id.".sh  > /dev/null &");
    }
}
