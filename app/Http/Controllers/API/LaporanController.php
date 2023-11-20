<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\LaporanModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\ExportModel;
use PhpParser\Node\Expr\Empty_;
// use PhpOffice\PhpSpreadsheet\Spreadsheet;
// use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanController extends Controller
{
	// public function __construct()
	// {
	// 	$this->middleware('auth:api');
	// }
	public function getLaporanPenjualan(Request $request)
	{
		$sql="CALL p_report_penjualan('$request->company_id','$request->awal','$request->akhir',$request->jenis,'$request->search','$request->order_col','$request->order_type',$request->limit,$request->length,$request->count_stats)";
		if (!empty($request->export) && $request->export == 1) {
			return $this->exportExcel($sql,'Laporan Penjualan');
		} else {
			if ($request->count_stats>0) {
				return DB::select($sql)[0];
			}else{
				return DB::select($sql);
			}
		}


	}
	public function getLaporanPembelian(Request $request)
	{
		$sql="CALL p_report_pembelian('$request->company_id','$request->awal','$request->akhir',$request->jenis,'$request->search','$request->order_col','$request->order_type',$request->limit,$request->limit,$request->count_stats)";
		if (!empty($request->export) && $request->export == 1) {
			return $this->exportExcel($sql,'Laporan Pembelian');
		} else {
			if ($request->count_stats>0) {
				return DB::select($sql)[0];
			}else{
				return DB::select($sql);
			}
		}
	}
	public function getLaporanHutang(Request $request)
	{
		$sql="CALL p_report_getHutangAktifPerperiode('$request->company_id', '$request->kd_supplier','$request->periode','$request->order_col','$request->order_type',$request->limit,$request->length,$request->count_stats)";
		if (!empty($request->export) && $request->export == 1) {
			return $this->exportExcel($sql,'Laporan Hutang');
		} else {
			if ($request->count_stats>0) {
				return DB::select($sql)[0];
			}else{
				return DB::select($sql);
			}
		}
	}

	public function getLaporanPiutang(Request $request)
	{
		$sql="CALL p_report_getPiutangAktifPerperiode('$request->company_id', '$request->kd_customer','$request->periode','$request->order_col','$request->order_type',$request->limit,$request->length,$request->count_stats)";
		if (!empty($request->export) && $request->export == 1) {
			return $this->exportExcel($sql,'Laporan Piutang');
		} else {
			if ($request->count_stats>0) {
				return DB::select($sql)[0];
			}else{
				return DB::select($sql);
			}
		}
	}

	public function getLaporanStok(Request $request)
	{
		$company_id = $request->company_id;
		$kd_barang = $request->kd_barang;
		$kd_divisi = $request->kd_divisi;
		$periode = $request->periode;
		$jenis = $request->jenis;
		$search = $request->search;
		$order_col = $request->order_col;
		$order_type = $request->order_type;
		$limit = $request->limit;
		$length = $request->length;
		$count_stats = $request->count_stats;
		$sql="CALL p_report_getStokAkhirFilter('$company_id', '$kd_barang', '$kd_divisi', '$periode', $jenis, '$search', '$order_col', '$order_type', $limit, $length, $count_stats)";
		if (!empty($request->export) && $request->export == 1) {
			return $this->exportExcel($sql,'Laporan Inventori');
		} else {
			if ($request->count_stats>0) {
				return DB::select($sql)[0];
			}else{
				return DB::select($sql);
			}
		}
	}

	public function getLaporanBiaya(Request $request)
	{
		$sql="CALL p_report_getBiayaOperasional('$request->company_id','$request->awal','$request->akhir',$request->jenis,'$request->search','$request->order_col','$request->order_type',$request->limit,$request->length,$request->count_stats)";
		if (!empty($request->export) && $request->export == 1) {
			return $this->exportExcel($sql,'Laporan Biaya Operasional');
		} else {
			if ($request->count_stats>0) {
				return DB::select($sql)[0];
			}else{
				return DB::select($sql);
			}
		}
	}

	public function getLaporanPendapatan(Request $request)
	{
		$sql="CALL p_report_getPendapatan('$request->company_id','$request->awal','$request->akhir',$request->jenis,'$request->search','$request->order_col','$request->order_type',$request->limit,$request->length,$request->count_stats)";
		if (!empty($request->export) && $request->export == 1) {
			return $this->exportExcel($sql,'Laporan Pendapatan');
		} else {
			if ($request->count_stats>0) {
				return DB::select($sql)[0];
			}else{
				return DB::select($sql);
			}
		}
	}

	public function getPenjualanOrder(Request $request)
	{
		$sql = "CALL p_report_penjualan_order('$request->company_id','$request->awal','$request->akhir',$request->jenis,'$request->search','$request->order_col','$request->order_type',$request->limit,$request->length,$request->count_stats)";

		if (!empty($request->export) && $request->export == 1) {
			return $this->exportExcel($sql,'Laporan Penjualan Order');
		} else {
			if ($request->count_stats>0) {
				return DB::select($sql)[0];
			}else{
				return DB::select($sql);
			}
		}
	}

	public function getPenjualanRetur(Request $request)
	{
		$sql = "CALL p_report_penjualan_retur('$request->company_id','$request->awal','$request->akhir',$request->jenis,'$request->search','$request->order_col','$request->order_type',$request->limit,$request->length,$request->count_stats)";
		if (!empty($request->export) && $request->export == 1) {
			return $this->exportExcel($sql,'Laporan Penjualan Retur');
		} else {
			if ($request->count_stats>0) {
				return DB::select($sql)[0];
			}else{
				return DB::select($sql);
			}
		}
	}

	public function getPembelianOrder(Request $request)
	{
		$sql = "CALL p_report_pembelian_order('$request->company_id','$request->awal','$request->akhir',$request->jenis,'$request->search','$request->order_col','$request->order_type',$request->limit,$request->length,$request->count_stats)";
		if (!empty($request->export) && $request->export == 1) {
			return $this->exportExcel($sql,'Laporan Pembelian Order');
		} else {
			if ($request->count_stats>0) {
				return DB::select($sql)[0];
			}else{
				return DB::select($sql);
			}
		}
	}

	public function getPembelianRetur(Request $request)
	{
		$sql = "CALL p_report_pembelian_retur('$request->company_id','$request->awal','$request->akhir',$request->jenis,'$request->search','$request->order_col','$request->order_type',$request->limit,$request->length,$request->count_stats)";
		if (!empty($request->export) && $request->export == 1) {
			return $this->exportExcel($sql,'Laporan Pembelian Retur');
		} else {
			if ($request->count_stats>0) {
				return DB::select($sql)[0];
			}else{
				return DB::select($sql);
			}
		}
	}

	public function getPenjualanNewBorn(Request $request)
	{
		$sql = "CALL p_report_penjualanNewBorn('$request->company_id','$request->awal','$request->akhir',$request->jenis, '$request->search', '$request->order_col', '$request->order_type', $request->limit,$request->length)";
		if (!empty($request->export) && $request->export == 1) {
			        // Get data to export from the database
					$body = DB::select($sql);
					$header = array_keys((array) $body[0]);
					$sqlData = collect($body)->map(function ($dt) {
						return [
							$dt->tanggal,
							$dt->jumlah,
							$dt->jumlah_tunai,
							$dt->jumlah_kredit,
							$dt->total_tunai,
						  	$dt->total_kredit
						];
					});

					return $this->exportExcelHana($header,$sqlData,'Laporan Penjualan');
		} else {
			$select = DB::select($sql);
			return response()->json($select);
		}
	}

	public function getPembelianNewBorn(Request $request)
	{
		$sql = "CALL p_report_pembelianNewBorn('$request->company_id','$request->awal','$request->akhir',$request->jenis, '$request->search', '$request->order_col', '$request->order_type',$request->limit,$request->length)";
		// return response()->json($sql);
		if (!empty($request->export) && $request->export == 1) {
			// Get data to export from the database
			$body = DB::select($sql);
			$header = array_keys((array) $body[0]);
			$sqlData = collect($body)->map(function ($dt) {
				return [
					$dt->tanggal,
					$dt->jumlah,
					$dt->jumlah_tunai,
					$dt->jumlah_kredit,
					$dt->total_tunai,
					$dt->total_kredit
				];
			});

			return $this->exportExcelHana($header,$sqlData,'Laporan Pembelian');
		} else {
			$select = DB::select($sql);
			return response()->json($select);
		}
	}

	public function produk(Request $request)
	{
		if ($request->jenis != 2) {
			$sql = "CALL misterkong_$request->company_id.p_mon_report_mutasi_stok('$request->awal','$request->akhir', $request->jenis, '$request->search', '$request->order_col', '$request->order_type', $request->limit, $request->length, 0)";
			$sql2 = "CALL misterkong_$request->company_id.p_mon_report_mutasi_stok('$request->awal','$request->akhir', $request->jenis, '$request->search', '$request->order_col', '$request->order_type', $request->limit, $request->length, 1)";
			if (!empty($request->export) && $request->export == 1) {
				return $this->exportExcel($sql,'Laporan Produk');
			} else {
				$query1 = DB::select($sql);
			 	$query2 = DB::select($sql2);
				return response()->json([
					'status' => 1,
					'error' => 0,
					'message' => count($query1) . ' Data ditemukan',
					'jumlah_record' => !empty($query2) ? $query2[0]->jumlah_record : 0,
					'data' => $query1
				]);
			}
		} else {
			$sql = LaporanModel::getKartuStok($request->company_id, $request->awal, $request->akhir, $request->limit, $request->length, 0, $request->kd_barang);
			$sql2 = LaporanModel::getKartuStok($request->company_id, $request->awal, $request->akhir, $request->limit, $request->length, 1, $request->kd_barang);
			if (!empty($request->export) && $request->export == 1) {
				return $this->exportExcel($sql,'Laporan Produk');
			} else {
				$query1 = DB::select($sql);
			 	$query2 = DB::select($sql2);
				return response()->json([
					'status' => 1,
					'error' => 0,
					'message' => count($query1) . ' Data ditemukan',
					'jumlah_record' => !empty($query2) ? $query2[0]->jumlah_record : 0,
					'data' => $query1
				]);
			}
		}
	}

	public function mutasi_kas(Request $request)
	{
		$sql = DB::select("CALL p_laporan_kas('$request->company_id','$request->awal','$request->akhir',$request->jenis, '$request->search', '$request->order_col', '$request->order_type', $request->limit, $request->length, '$request->kd_kas')");
		return response()->json($sql);
	}

	public function getLaporanBiayaNewBorn(Request $request)
	{
		$sql = "CALL p_biayaOperasionalNewBorn('$request->company_id','$request->awal','$request->akhir','$request->search','$request->order_col','$request->order_type','$request->limit',$request->length,$request->count_stats)";
		if (!empty($request->export) && $request->export == 1) {
			// Get data to export from the database
			$body = DB::select($sql);
			$header = array_keys((array) $body[0]);
			$sqlData = collect($body)->map(function ($dt) {
				return [
					$dt->tanggal,
					$dt->nama_biaya,
					$dt->nominal,
					$dt->keterangan
				];
			});
			return $this->exportExcelHana($header,$sqlData,'Laporan Biaya Operasional');
		} else {
			// $select = DB::select($sql);
			// return response()->json($select);
			if ($request->count_stats > 0) {
				return DB::select($sql)[0];
			} else {
				return DB::select($sql);
			}
		}
	}

	public function getLaporanPendapatanNewBorn(Request $request)
	{
		$sql = "CALL p_pendapatanNewBorn('$request->company_id','$request->awal','$request->akhir','$request->search','$request->order_col','$request->order_type','$request->limit',$request->length,$request->count_stats)";
		if (!empty($request->export) && $request->export == 1) {
			// Get data to export from the database
			$body = DB::select($sql);
			$header = array_keys((array) $body[0]);
			$sqlData = collect($body)->map(function ($dt) {
				return [
					$dt->tanggal,
					$dt->nama_pendapatan,
					$dt->nominal,
					$dt->keterangan
				];
			});
			return $this->exportExcelHana($header,$sqlData,'Laporan Pendapatan');
		} else {
			if ($request->count_stats > 0) {
				return DB::select($sql)[0];
			} else {
				return DB::select($sql);
			}
		}
	}
	function exportExcel($sql,$judul)
	{
		$sql = $sql;
		// $export= new ExportModel();
		$rs = ExportModel::exportExcel($sql, $judul);


		// Set the headers for the Excel file download
		$headers = [
			'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'Content-Disposition' => "attachment; filename='$judul.xlsx'",
		];
		// Stream the Excel file to the browser
		return response()->streamDownload(function () use ($rs) {
			$rs->save('php://output');
		}, "$judul.xlsx", $headers);
		// $export->exportExcel($sql);

	}

	function exportExcelHana($header,$sqlData,$judul)
	{
		// Create a new instance of the Spreadsheet class
		$spreadsheet = new Spreadsheet();
					
		// Add data to the first worksheet
		$worksheet1 = $spreadsheet->getActiveSheet();
		$worksheet1->setTitle('Users');
		$worksheet1->setCellValue('A1', $judul); // Add title to the worksheet
		$worksheet1->mergeCells('A1:C1'); // Merge cells for title
		// $worksheet1->mergeCells('G1:G11'); // Merge cells for title

		$worksheet1->fromArray([$header], null, 'A2');
		$worksheet1->fromArray($sqlData->toArray(), null, 'A3');

		// Create a new instance of the Xlsx writer class
		$writer = new Xlsx($spreadsheet);

		// Set the headers for the Excel file download
		$headers = [
			'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'Content-Disposition' => "attachment; filename='$judul.xlsx'",
		];

		// Stream the Excel file to the browser
		return response()->streamDownload(function () use ($writer) {
			$writer->save('php://output');
		},  "$judul.xlsx", $headers);
	}
	function exportExcelHana2($query, $judul)
    {

        $body = DB::select($query);
        $header = array_keys((array) $body[0]);
        $sqlData = collect($body)->map(function ($dt) {
            return [
                // $dt->tanggal,
                // $dt->jumlah,
                // $dt->jumlah_tunai,
                // $dt->jumlah_kredit,
                // $dt->total_tunai,
                // $dt->total_kredit
            ];
        });

        // Create a new instance of the Spreadsheet class
		$spreadsheet = new Spreadsheet();
					
		// Add data to the first worksheet
		$worksheet1 = $spreadsheet->getActiveSheet();
		$worksheet1->setTitle('Users');
		$worksheet1->setCellValue('A1', $judul); // Add title to the worksheet
		$worksheet1->mergeCells('A1:C1'); // Merge cells for title
		// $worksheet1->mergeCells('G1:G11'); // Merge cells for title

		$worksheet1->fromArray([$header], null, 'A2');
		// $worksheet1->fromArray($sqlData->toArray(), null, 'A3');

		// Create a new instance of the Xlsx writer class
		$writer = new Xlsx($spreadsheet);

		// Set the headers for the Excel file download
		$headers = [
			'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'Content-Disposition' => "attachment; filename='$judul.xlsx'",
		];

		// Stream the Excel file to the browser
		return response()->streamDownload(function () use ($writer) {
			$writer->save('php://output');
		},  "$judul.xlsx", $headers);
    }

	public function getFmiSmiStock(Request $request)
	{
		// $sql = "CALL p_fmi_smi_stock('$request->company_id','$request->awal','$request->akhir','$request->search','$request->order_col','$request->order_type','$request->limit',$request->length,$request->count_stats)";
		$result1 = DB::select("CALL p_fmi_smi_stock('$request->company_id','$request->awal','$request->akhir','$request->search','$request->order_col','$request->order_type','$request->limit',$request->length,0)");
		$result2 = DB::select("CALL p_fmi_smi_stock('$request->company_id','$request->awal','$request->akhir','$request->search','$request->order_col','$request->order_type',0,10,1)");
		return response()->json([
			'error' => 0,
            'message' => 'Data Found',
            'jumlah_record' => $result2[0]->jumlah_record,
            'data' => $result1
		]);
	}
}
