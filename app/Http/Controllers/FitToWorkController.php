<?php

namespace App\Http\Controllers;

use App\Models\FitToWork;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FitToWorkController extends Controller
{
    // 1. Menampilkan Tabel Data
    public function index()
    {
        $user = Auth::user();
        
        // Medis, QHSE, dan Superadmin bisa melihat semua data
        if ($user->hasAnyRole(['superadmin', 'qhse', 'medis'])) {
            $ftws = FitToWork::latest()->get();
        } 
        // Pekerja hanya melihat pengajuannya sendiri
        else {
            $ftws = FitToWork::where('user_id', $user->id)->latest()->get();
        }

        return view('fit_to_work.index', compact('ftws'));
    }

    // 2. Menampilkan Form Pengajuan (Hanya untuk Pekerja)
    public function create()
    {
        return view('fit_to_work.create');
    }

    // 3. Menyimpan Pengajuan Awal (Oleh Pekerja)
    public function store(Request $request)
    {
        $request->validate([
            'age'             => 'required|integer|min:17',
            'sleep_hours'     => 'required|integer|min:0',
            'job_type'        => 'required|string|max:255',
            'q1_is_fit'       => 'required|boolean',
            'q2_accustomed'   => 'required|boolean',
            'q3_vision_issue' => 'required|boolean',
            'q4_asthma'       => 'required|boolean',
            'q5_epilepsy'     => 'required|boolean',
            'q6_trained'      => 'required|boolean',
        ]);

        $data = $request->all();
        $data['user_id'] = Auth::id();
        $data['status']  = 'Pending_Medis'; // Status langsung antre ke Medis

        FitToWork::create($data);

        return redirect()->route('fit-to-work.index')
            ->with('success', 'Form Kuesioner berhasil dikirim! Silakan menuju ruang pemeriksaan medis.');
    }

    // 4. Menampilkan Halaman Detail
    public function show(FitToWork $fitToWork)
    {
        return view('fit_to_work.show', compact('fitToWork'));
    }

    // 5. Menampilkan Halaman Proses/Pemeriksaan (Oleh Medis / QHSE)
    public function edit(FitToWork $fitToWork)
    {
        // Pekerja biasa tidak boleh mengakses halaman ini
        if (!Auth::user()->hasAnyRole(['superadmin', 'qhse', 'medis'])) {
            abort(403, 'Akses Ditolak: Hanya petugas yang berwenang.');
        }

        return view('fit_to_work.edit', compact('fitToWork'));
    }

    // 6. Menyimpan Hasil Pemeriksaan (Multi-level Approval)
    public function update(Request $request, FitToWork $fitToWork)
    {
        $user = Auth::user();

        // --- SKENARIO 1: YANG MEMPROSES ADALAH MEDIS/DOKTER ---
        if ($user->hasRole('medis') && $fitToWork->status == 'Pending_Medis') {
            $request->validate([
                'blood_pressure'   => 'required|string',
                'body_temp'        => 'required|string',
                'spo2'             => 'required|string',
                'heart_rate'       => 'required|string',
                'respiration_rate' => 'required|string',
                'is_recommended'   => 'required|boolean',
            ]);

            $fitToWork->update([
                'blood_pressure'   => $request->blood_pressure,
                'body_temp'        => $request->body_temp,
                'spo2'             => $request->spo2,
                'heart_rate'       => $request->heart_rate,
                'respiration_rate' => $request->respiration_rate,
                'is_recommended'   => $request->is_recommended,
                'medical_id'       => $user->id,
                'status'           => 'Pending_QHSE', // Lempar ke QHSE
            ]);

            return redirect()->route('fit-to-work.index')
                ->with('success', 'Pemeriksaan fisik selesai. Form diteruskan ke tim QHSE untuk validasi.');
        }

        // --- SKENARIO 2: YANG MEMPROSES ADALAH QHSE / SUPERADMIN ---
        if ($user->hasAnyRole(['superadmin', 'qhse']) && $fitToWork->status == 'Pending_QHSE') {
            $request->validate([
                'status' => 'required|in:Approved,Rejected'
            ]);

            $fitToWork->update([
                'status'  => $request->status,
                'qhse_id' => $user->id,
            ]);

            return redirect()->route('fit-to-work.index')
                ->with('success', 'Validasi akhir Fit to Work berhasil disimpan!');
        }

        abort(403, 'Akses Ditolak atau Status Dokumen tidak valid untuk diproses.');
    }

    // 7. Menghapus Data (Hanya Superadmin/QHSE)
    public function destroy(FitToWork $fitToWork)
    {
        if (!Auth::user()->hasAnyRole(['superadmin', 'qhse'])) {
            abort(403, 'Akses Ditolak.');
        }

        $fitToWork->delete();
        return redirect()->route('fit-to-work.index')->with('success', 'Data Fit to Work dihapus!');
    }
}