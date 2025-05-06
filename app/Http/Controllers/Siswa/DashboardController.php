<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // $siswa = Auth::user()->siswa; // Dapatkan data siswa yang login
        // Ambil data nilai terbaru, jadwal ujian, dll.
        return view('siswa.dashboard');
    }
}