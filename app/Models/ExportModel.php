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

    function exportExcel($query,$judul)
    {
        $sql = DB::select($query);
        $spreadsheet = new Spreadsheet();
        $worksheet1 = $spreadsheet->getActiveSheet();

        $worksheet1->setCellValue('A1', "$judul");

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

    function exportExcelHana($query, $judul)
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
}
