<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Utilites extends Controller
{
    public function AlterDb()
    {
        return view('alterDb');
    }
}
