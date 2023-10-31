<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\DB;

class ExportModel extends Model
{
    use HasFactory;

    function exportExcel($query)
    {
        $sql = DB::select($query);
        $spreadsheet = new Spreadsheet();
        $worksheet1 = $spreadsheet->getActiveSheet();

        $worksheet1->setCellValue('A1', 'Laporan Penjualan');

        foreach ($sql as $r => $item) {
            $alphabet='A';
            
            // Add data to the first worksheet
            foreach ($item as $key => $value) {
                $worksheet1->setCellValue($alphabet .'2', $key);
                $worksheet1->setCellValue($alphabet . ($r+3), $value);
                $alphabet++;
            }             
        }
        // return $spreadsheet;
        $writer = new Xlsx($spreadsheet);
        return $writer;
    }
}
