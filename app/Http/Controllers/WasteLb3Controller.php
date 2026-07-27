<?php

namespace App\Http\Controllers;

use App\Models\WasteLb3;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class WasteLb3Controller extends Controller
{
    public function index()
    {
        $lb3s = WasteLb3::orderBy('created_at', 'desc')->get();
        return view('waste_lb3.index', compact('lb3s'));
    }

    public function create()
    {
        return view('waste_lb3.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'waste_type'      => 'required|string|max:255',
            'quantity'        => 'required|numeric|min:0',
            'unit'            => 'required|string|max:50',
            'source_location' => 'required|string|max:255',
        ]);

        $data = $request->all();
        $data['user_id'] = Auth::id(); 
        $data['status']  = 'Pending'; // Status awal sebelum diterima TPS

        WasteLb3::create($data);

        return redirect()->route('waste-lb3.index')
            ->with('success', 'Request penyerahan Limbah B3 berhasil dibuat!');
    }

    public function show(WasteLb3 $wasteLb3)
    {
        return view('waste_lb3.show', compact('wasteLb3'));
    }

    public function edit(WasteLb3 $wasteLb3)
    {
        // Halaman ini khusus untuk Petugas TPS melakukan Tanda Tangan Digital
        return view('waste_lb3.edit', compact('wasteLb3'));
    }

    public function update(Request $request, WasteLb3 $wasteLb3)
    {
        $request->validate([
            'status'        => 'required|in:Pending,Received',
            'receiver_name' => 'nullable|string|max:255',
            'signature'     => 'nullable|string' // Ini adalah string base64 dari Canvas
        ]);

        $data = $request->except(['signature']);

        // LOGIKA PENYIMPANAN TANDA TANGAN DIGITAL
        if ($request->filled('signature')) {
            // Memisahkan metadata base64 dengan data aslinya
            $image_parts = explode(";base64,", $request->signature);
            if (count($image_parts) == 2) {
                $image_base64 = base64_decode($image_parts[1]);
                $fileName = 'signatures/lb3_' . time() . '_' . uniqid() . '.png';
                
                // Simpan gambar ke folder storage/app/public/signatures
                Storage::disk('public')->put($fileName, $image_base64);
                
                // Hapus signature lama jika ada (untuk menghemat memori)
                if ($wasteLb3->signature_path && Storage::disk('public')->exists($wasteLb3->signature_path)) {
                    Storage::disk('public')->delete($wasteLb3->signature_path);
                }
                
                $data['signature_path'] = $fileName;
                $data['received_at'] = now();
            }
        }

        $wasteLb3->update($data);

        return redirect()->route('waste-lb3.show', $wasteLb3->id)
            ->with('success', 'Serah terima limbah B3 berhasil ditandatangani!');
    }

    public function destroy(WasteLb3 $wasteLb3)
    {
        // Hapus file signature fisik dari storage jika datanya dihapus
        if ($wasteLb3->signature_path && Storage::disk('public')->exists($wasteLb3->signature_path)) {
            Storage::disk('public')->delete($wasteLb3->signature_path);
        }
        
        $wasteLb3->delete();
        return redirect()->route('waste-lb3.index')
            ->with('success', 'Data request LB3 berhasil dibatalkan!');
    }
}