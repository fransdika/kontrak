<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\DB;
use App\Models\ExportModel;

class ExportController extends Controller
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
    }
    function exportExcel($sql)
    {
        $sql =$sql;
        // $export= new ExportModel();
        $rs=ExportModel::exportExcel($sql);


        
        // Set the headers for the Excel file download
        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="Rekap Order Per-customer.xlsx"',
        ];
        // Stream the Excel file to the browser
        return response()->streamDownload(function () use ($rs) {
            $rs->save('php://output');
        }, 'Rekap Order Per-customer.xlsx', $headers);
        // $export->exportExcel($sql);
        
    }
    public function export2(Request $request)
    {
        // Get data to export from the database
        $barangs = [
            ['kd_barang'=>'aaa','nama'=>'nnn'],
            ['kd_barang'=>'aaa','nama'=>'nnn']
        ];

        $json = json_decode($request->getContent(), true);
        // echo "<pre>";
        // print_r($json);
        // echo "</pre>";
        // $banners = bannerModel::customBanner()->get();

        // Define the headings for the Excel file
        $barangHeadings = array_keys($json[0]);
        $bannerHeadings = ['ID', 'Nama Banner'];
        
        // Map the data to an array of arrays
        // $barangData = collect($json)->map(function ($barang) {
        //     return [
        //         $barang['kd_barang'],
        //         $barang['nama']
        //     ];
        // });
        $barangData=[];
        foreach ($json as $key_json => $value_json) {
            foreach ($value_json as $key_val => $value_val) {
                $barangData[$key_json][]=$value_val;
            }
        }

        // $bannerData = $banners->map(function ($banner) {
        //     return [
        //         $banner->id,
        //         $banner->nama
        //     ];
        // });

        // Create a new instance of the Spreadsheet class
        $spreadsheet = new Spreadsheet();

        // Add data to the first worksheet
        $worksheet1 = $spreadsheet->getActiveSheet();
        $worksheet1->getStyle('A')->getNumberFormat()->setFormatCode('@');
        $worksheet1->getColumnDimension('A')->setWidth(20);
        $worksheet1->getColumnDimension('B')->setWidth(45);

        $worksheet1->setTitle('Stok');
        $worksheet1->setCellValue('A1', 'Data Stok akan Habis / Rekomendasi Order'); // Add title to the worksheet
        $worksheet1->mergeCells('A1:C1'); // Merge cells for title
        // $worksheet1->mergeCells('G1:G11'); // Merge cells for title
        

        $worksheet1->fromArray([$barangHeadings], null, 'A2');
        $worksheet1->fromArray($barangData, null, 'A3');

        // // Add data to the second worksheet
        // $worksheet2 = $spreadsheet->createSheet();
        // $worksheet2->setTitle('Orders');
        // $worksheet2->setCellValue('A1', 'Order Data'); // Add title to the worksheet
        // $worksheet2->mergeCells('A1:C1'); // Merge cells for title
        // $worksheet2->fromArray([$bannerHeadings]);
        // $worksheet2->fromArray($bannerData->toArray(), null, 'A2');

        // Create a new instance of the Xlsx writer class
        $writer = new Xlsx($spreadsheet);
        // Set the headers for the Excel file download
        $nama_file="limited_stocks.xlsx";
        $path = "../../../public_html/back_end_mp/laporan/".$nama_file;
        $writer->save($path);


        $pesan="Hi%20Kak,%20stokmu%20mau%20habis%20nih,%20cek%20disini%20ya%0A%0Ahttps://misterkong.com/back_end_mp/laporan/".$nama_file;
        // $pesan="Yang,%20jangan%20nangis%20ya%20ini%20buat%20kamu%20".urlencode('I love you ♥')."%0A%0Ahttps://misterkong.com/back_end_mp/laporan/".$nama_file;
        $this->callAPIWhatsapp($pesan);
        return response()->json(['message' => 'Excel file created and saved.']);
    }

    public function desktopExportCustom(Request $request, $jenis)
    {
        
        $json = json_decode($request->getContent(), true);
        // Define the headings for the Excel file
        $barangHeadings = array_keys($json[0]);

        $barangData=[];
        foreach ($json as $key_json => $value_json) {
            foreach ($value_json as $key_val => $value_val) {
                $barangData[$key_json][]=$value_val;
            }
        }

        $pesan='';
        $nama_file='';
        $headers='';
        if ($jenis=='stok-minus') {
            $nama_file="stock-minus.xlsx";
            $pesan="Hi%20Kak,%20stokmu%20ada%20yang%20minus%20nih,mohon%20dicek%20ya%20cek%20disini%20ya%0A%0Ahttps://misterkong.com/back_end_mp/laporan/".$nama_file;
            $headers='Daftar Barang stok minus';
        }elseif($jenis=='hutang-supplier'){
            $nama_file="Hutang-supplier.xlsx";
            $pesan="Hai%20kak%20kita%20punya%20catatan%20kecil%20nih,%20ada%20beberapa%20tagihan%20yang%20harus%20dilunasi%20ke%20supplier%kakak.%20Cek%20daftarnya%20disini%20ya%0A%0Ahttps://misterkong.com/back_end_mp/laporan/".$nama_file;
            $headers='Daftar Tagihan Supplier';
        }elseif($jenis=='rekap-penjualan-harian'){
            $yesterday=date('Y-m-d',strtotime(' - 1 days'));
            $nama_file="Rekap-penjualan_$yesterday.xlsx";
            $pesan="Hai%20kak%20ini%20ya%20rekapan%20penjualan%20kakak%20untuk%20tanggal%20$yesterday%0A%0Ahttps://misterkong.com/back_end_mp/laporan/".$nama_file;
            $headers="Rekap Penjualan $yesterday";
        }

        $spreadsheet = new Spreadsheet();

        // Add data to the first worksheet
        $worksheet1 = $spreadsheet->getActiveSheet();
        $worksheet1->getStyle('A')->getNumberFormat()->setFormatCode('@');
        $worksheet1->getColumnDimension('A')->setWidth(20);
        $worksheet1->getColumnDimension('B')->setWidth(45);

        $worksheet1->setTitle('Stok');
        $worksheet1->setCellValue('A1', $headers); // Add title to the worksheet
        $worksheet1->mergeCells('A1:C1'); // Merge cells for title
        

        $worksheet1->fromArray([$barangHeadings], null, 'A2');
        $worksheet1->fromArray($barangData, null, 'A3');

        // Create a new instance of the Xlsx writer class
        $writer = new Xlsx($spreadsheet);
        // Set the headers for the Excel file download
        // $nama_file="limited_stocks.xlsx";

        

        $path = "../../../public_html/back_end_mp/laporan/".$nama_file;
        $writer->save($path);


        $this->callAPIWhatsapp($pesan);
        return response()->json(['message' => 'Excel file created and saved.']);
    }

    public function callAPIWhatsapp($payload)
    {
        if (!empty($payload)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://kongbot.api.misterkong.com/kirimPesanCustome?nowhatsapp=085237342776__087864649100__081703396989__081803742129&isiPesan='.$payload."&token=AEtU5bVZ6qQTzCXGlX1daJMRwyUPpGft");
        // __081703396989__081803742129
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
            $output = curl_exec($ch); 
            curl_close($ch);
            return $output;
        }    
    }


    

}
