<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DatabaseGeneratorModel extends Model
{
    use HasFactory;
    public function getTableList()
    {
        $sql = "SHOW full tables FROM misterkong_comp2020061905541701 where Table_Type = 'BASE TABLE'";
        $data = DB::select($sql);
        $list_table = [];
        foreach ($data as $key => $value) {
            $list_table[] = $value->Tables_in_misterkong_comp2020061905541701;
        }
        return $this->getCreateTable($list_table);
    }
    public function getCreateTable($list_table)
    {
        foreach ($list_table as $key => $value) {
            $sql_get_created_tbl = "SHOW CREATE TABLE misterkong_comp2020061905541701.$value";
            $data = DB::select($sql_get_created_tbl);
            $data_create_table[] = $data[0]->{'Create Table'};
        }
        return $data_create_table;
    }

    public function getTriggerList()
    {
        $sql = "SHOW TRIGGERS FROM misterkong_comp2020061905541701";
        $data = DB::select($sql);
        $list_trigger = [];
        foreach ($data as $key => $value) {
            $list_trigger[] = $value->Trigger;
        }
        return $this->getCreateTrigger($list_trigger);
    }
    public function getCreateTrigger($list_trigger)
    {
        // $replaceable='DEFINER=`root`@`localhost` '; //local
        $replaceable='DEFINER=`remote`@`localhost` '; //vps
        foreach ($list_trigger as $key => $value) {
            $sql_get_created_trigger = "SHOW CREATE TRIGGER misterkong_comp2020061905541701.$value";
            $data = DB::select($sql_get_created_trigger);
            $data_create_trigger[]=str_replace($replaceable, '', "DELIMITER //\n".$data[0]->{'SQL Original Statement'}."\n//");
        }
        return $data_create_trigger;
    }
}
