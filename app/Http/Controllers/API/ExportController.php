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
    

}
