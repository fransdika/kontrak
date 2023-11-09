<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;
class MapperModel extends Model
{
    use HasFactory;
    function getKontrakUser($other_cid){
        $sql="CALL misterkong_mapper.getListKontrakMapper($other_cid)";
        $data=DB::select($sql);
        return $data;
    }
    function getTarifMapper(){
        $data=DB::table('misterkong_mapper.m_tarif_mapper')->where(['status'=>1])->get();
        return $data;
    }
    public function updatePembayaran($data_save=[],$condition=[])
    {
        DB::beginTransaction();
        try {
            DB::table('misterkong_mapper.t_pembayaran_mapper')->where($condition)->update($data_save);
            DB::commit();    
            return 1;
        } catch (\Exception $e) {
            echo $e->getMessage();
            DB::rollback();
            return 0;
        }
        
    }


}
