<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class LaporanTableController extends Controller
{
    public function laporanp_priode(Request $request)
    {
        $company_id=$request->company_id;
		$awal=$request->awal;
		$akhir=$request->akhir;
		$jenis=$request->jenis;
		$limit=$request->limit;
		$length=$request->length;
		$data = Laporan::laporanpenjualan($company_id,$kd_supplier,$periode,$limit_start,$limit_length);
		return DataTables::of($data)->tojson();
    }
    public function laporanpem_priode(Request $request)
    {
        
        $company_id=$request->company_id;
		$awal=$request->awal;
		$akhir=$request->akhir;
		$jenis=$request->jenis;
		$limit=$request->limit;
		$length=$request->length;
		$data = Laporan::pembelian($company_id,$awal,$akhir,$jenis,$limit,$length);	
		return DataTables::of($data)->tojson();
    }

    public function inventory(Request $request)
    {
        $company_id=$request->company_id;
		$kd_barang=$request->kd_barang;
		$kd_divisi=$request->kd_divisi;
		$periode=$request->periode;
		$limit=$request->limit;
		$length=$request->length;
		$data = Laporan::inventori($company_id,$kd_barang,$kd_divisi,$periode,$limit,$length);	
		return DataTables::of($data)->tojson();
    }
    public function hutang(Request $request)
    {
        $company_id=$request->company_id;
		$kd_supplier=$request->kd_supplier;
		$periode=$request->periode;
		$limit=$request->limit;
		$length=$request->length;
		$data = Laporan::hutang($company_id,$kd_supplier,$periode,$limit_start,$limit_length);
        return DataTables::of($data)->tojson();
    }
     public function piutang(Request $request)
     {
        $company_id=$request->company_id;
		$kd_customer=$request->kd_customer;
		$periode=$request->periode;
		$limit=$request->limit;
		$length=$request->length;
		$data = Laporan::piutang($company_id,$kd_supplier,$periode,$limit_start,$limit_length);
		return DataTables::of($data)->tojson();
     }
}
