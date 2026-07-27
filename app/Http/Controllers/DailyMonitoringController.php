<?php

namespace App\Http\Controllers;

use App\Models\DailyMonitoring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DailyMonitoringController extends Controller
{
    public function index()
    {
        // Urutkan berdasarkan tanggal terbaru
        $monitorings = DailyMonitoring::orderBy('date', 'desc')->latest()->get();
        return view('daily_monitoring.index', compact('monitorings'));
    }

    public function create()
    {
        return view('daily_monitoring.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'        => 'required|date',
            'location'    => 'required|in:Settling Pond 2,Settling Pond 3,Limbah Domestik',
            'ph_level'    => 'required|numeric|min:0|max:14',
            'debit_level' => 'nullable|numeric|min:0', // Nullable karena Settling Pond 2 tidak butuh debit
            'notes'       => 'nullable|string'
        ]);

        $data = $request->all();
        $data['user_id'] = Auth::id();

        // Kosongkan nilai debit jika lokasinya Settling Pond 2 (untuk jaga-jaga)
        if ($data['location'] == 'Settling Pond 2') {
            $data['debit_level'] = null;
        }

        DailyMonitoring::create($data);

        return redirect()->route('daily-monitoring.index')
            ->with('success', 'Data Pemantauan Harian berhasil ditambahkan!');
    }

    public function show(DailyMonitoring $dailyMonitoring)
    {
        return view('daily_monitoring.show', compact('dailyMonitoring'));
    }

    public function edit(DailyMonitoring $dailyMonitoring)
    {
        return view('daily_monitoring.edit', compact('dailyMonitoring'));
    }

    public function update(Request $request, DailyMonitoring $dailyMonitoring)
    {
        $request->validate([
            'date'        => 'required|date',
            'location'    => 'required|in:Settling Pond 2,Settling Pond 3,Limbah Domestik',
            'ph_level'    => 'required|numeric|min:0|max:14',
            'debit_level' => 'nullable|numeric|min:0',
            'naoh_amount' => 'nullable|integer|min:0', // <--- TAMBAHAN
            'notes'       => 'nullable|string'
        ]);

        $data = $request->all();
        
        if ($data['location'] == 'Settling Pond 2') {
            $data['debit_level'] = null;
        }

        $dailyMonitoring->update($data);

        return redirect()->route('daily-monitoring.index')
            ->with('success', 'Data Pemantauan Harian berhasil diperbarui!');
    }

    public function destroy(DailyMonitoring $dailyMonitoring)
    {
        $dailyMonitoring->delete();
        return redirect()->route('daily-monitoring.index')
            ->with('success', 'Data berhasil dihapus!');
    }
}