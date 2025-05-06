<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Di sini Anda bisa mengambil data untuk statistik admin nantinya
        return view('admin.dashboard');
    }
}