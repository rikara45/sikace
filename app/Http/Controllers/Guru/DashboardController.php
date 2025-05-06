<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // $guru = Auth::user()->guru; // Dapatkan data guru yang login
        // Ambil data kelas yang diajar, nilai belum diinput, dll.
        return view('guru.dashboard');
    }
}