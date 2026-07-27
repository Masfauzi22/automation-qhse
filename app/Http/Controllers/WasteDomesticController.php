<?php

namespace App\Http\Controllers;

use App\Models\WasteDomestic;
use App\Models\WasteDomesticBast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class WasteDomesticController extends Controller
{
    public function index()
    {
        // 1. Ambil semua data harian, lalu kelompokkan berdasarkan "Bulan-Tahun" dan "Lokasi"
        $wastes = WasteDomestic::orderBy('collection_date', 'desc')->get()->groupBy(function($item) {
            return Carbon::parse($item->collection_date)->format('Y-m') . '|' . $item->location;
        });

        $monthlyRecaps = [];

        // 2. Olah data yang sudah dikelompokkan menjadi total bulanan
        foreach($wastes as $key => $group) {
            list($monthYear, $location) = explode('|', $key);
            
            // 3. Cari apakah bulan & lokasi ini sudah punya file BAST di tabel terpisah
            $bast = WasteDomesticBast::where('month_year', $monthYear)
                                     ->where('location', $location)
                                     ->first();

            $monthlyRecaps[] = (object)[
                'month_year'      => $monthYear, // Contoh: "2026-07"
                'location'        => $location,
                'total_organic'   => $group->sum('organic_volume'),
                'total_anorganic' => $group->sum('anorganic_volume'),
                'total_residue'   => $group->sum('residue_volume'),
                'bast_status'     => $bast ? $bast->status : 'Menunggu BAST',
                'bast_id'         => $bast ? $bast->id : null,
            ];
        }

        return view('waste_domestic.index', compact('monthlyRecaps'));
    }

    public function create()
    {
        return view('waste_domestic.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'collection_date'  => 'required|date',
            'location'         => 'required|in:Coal Terminal,Fuel Terminal',
            'organic_volume'   => 'required|numeric|min:0',
            'anorganic_volume' => 'required|numeric|min:0',
            'residue_volume'   => 'required|numeric|min:0',
            'notes'            => 'nullable|string'
        ]);

        $data = $request->all();
        $data['user_id'] = Auth::id(); // Mencatat siapa petugasnya

        WasteDomestic::create($data);

        return redirect()->route('waste-domestic.index')
            ->with('success', 'Data angkutan sampah berhasil disimpan!');
    }

    public function show(WasteDomestic $wasteDomestic)
    {
        return view('waste_domestic.show', compact('wasteDomestic'));
    }

    public function edit(WasteDomestic $wasteDomestic)
    {
        return view('waste_domestic.edit', compact('wasteDomestic'));
    }

    public function update(Request $request, WasteDomestic $wasteDomestic)
    {
        $request->validate([
            'collection_date'  => 'required|date',
            'location'         => 'required|in:Coal Terminal,Fuel Terminal',
            'organic_volume'   => 'required|numeric|min:0',
            'anorganic_volume' => 'required|numeric|min:0',
            'residue_volume'   => 'required|numeric|min:0',
            'notes'            => 'nullable|string'
        ]);

        $data = $request->all();

        $wasteDomestic->update($data);

        return redirect()->route('waste-domestic.index')
            ->with('success', 'Data angkutan sampah berhasil diperbarui!');
    }

    public function destroy(WasteDomestic $wasteDomestic)
    {
        $wasteDomestic->delete();
        return redirect()->route('waste-domestic.index')
            ->with('success', 'Data sampah berhasil dihapus!');
    }


    // ==========================================
    // FUNGSI BARU UNTUK REKAP BULANAN & BAST
    // ==========================================
    
    // ==========================================
    // FUNGSI UNTUK REKAP BULANAN & BAST
    // ==========================================
    
    public function recapDetail(Request $request)
    {
        $monthYear = $request->query('month'); // Contoh: "2026-08"
        $location = $request->query('location'); // Contoh: "Coal Terminal"

        // 1. Ambil rincian data sampah harian di bulan dan lokasi tersebut
        $dailyData = WasteDomestic::where('location', $location)
            ->where('collection_date', 'like', $monthYear . '%')
            ->orderBy('collection_date', 'asc')
            ->get();

        // 2. Cari data BAST-nya, jika belum ada di database, buat otomatis dengan status 'Menunggu BAST'
        $bast = WasteDomesticBast::firstOrCreate(
            ['month_year' => $monthYear, 'location' => $location],
            ['status' => 'Menunggu BAST']
        );

        // 3. Hitung total volume bulan ini
        $totalOrganic = $dailyData->sum('organic_volume');
        $totalAnorganic = $dailyData->sum('anorganic_volume');
        $totalResidue = $dailyData->sum('residue_volume');

        return view('waste_domestic.recap', compact('dailyData', 'bast', 'monthYear', 'location', 'totalOrganic', 'totalAnorganic', 'totalResidue'));
    }

    public function uploadBast(Request $request)
    {
        $request->validate([
            'bast_id'   => 'required|exists:waste_domestic_basts,id',
            'bast_file' => 'required|mimes:pdf|max:5120', // Wajib PDF, maksimal 5MB
        ]);

        $bast = WasteDomesticBast::findOrFail($request->bast_id);

        if ($request->hasFile('bast_file')) {
            $file = $request->file('bast_file');
            
            // Membuat nama file yang rapi: BAST_Coal_Terminal_2026-08_123456.pdf
            $filename = 'BAST_' . str_replace(' ', '_', $bast->location) . '_' . $bast->month_year . '_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Simpan ke folder storage/app/public/bast_documents
            $path = $file->storeAs('bast_documents', $filename, 'public');

            // Update database
            $bast->update([
                'bast_file_path' => $path,
                'status'         => 'Menunggu Validasi',
                'uploaded_by'    => Auth::id(), // Catat user (GA) yang upload
            ]);
        }

        return redirect()->back()->with('success', 'Dokumen BAST berhasil diunggah! Menunggu validasi dari tim QHSE.');
    }

    public function validateBast(Request $request)
    {
        $request->validate([
            'bast_id' => 'required|exists:waste_domestic_basts,id',
        ]);

        $bast = WasteDomesticBast::findOrFail($request->bast_id);
        
        // Update status jadi Tervalidasi
        $bast->update([
            'status'       => 'Tervalidasi',
            'validated_by' => Auth::id(), // Catat user (QHSE) yang validasi
        ]);

        return redirect()->back()->with('success', 'Dokumen BAST telah berhasil divalidasi!');
    }
}