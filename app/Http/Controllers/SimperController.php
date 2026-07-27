<?php

namespace App\Http\Controllers;

use App\Models\Simper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class SimperController extends Controller
{
    // 1. Menampilkan halaman utama (Tabel)
    public function index()
    {
        $user = Auth::user();
        
        // Superadmin & QHSE bisa melihat SEMUA data
        if ($user->hasAnyRole(['superadmin', 'qhse'])) {
            $simpers = Simper::latest()->get();
        } 
        // Jika role adalah atasan/PIC atau pekerja biasa, kita sesuaikan filter atau tampilkan miliknya
        else {
            $simpers = Simper::where('user_id', $user->id)->latest()->get();
        }

        return view('simper.index', compact('simpers'));
    }

    // 2. Menampilkan form tambah/request
    public function create()
    {
        return view('simper.create');
    }

    // 3. Menyimpan data pengajuan ke database (Oleh Pekerja)
    public function store(Request $request)
    {
        $user = Auth::user();
        $isQhse = $user->hasAnyRole(['superadmin', 'qhse']);

        $rules = [
            'name'            => 'required|string|max:255',
            'nrp'             => 'nullable|string|max:255',
            'company'         => 'required|string|max:255',
            'department'      => 'required|string|max:255',
            'blood_type'      => 'required|in:A,B,AB,O',
            'position'        => 'required|string|max:255',
            'police_license'  => 'required|string|max:255',
            'photo_path'      => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];

        if ($isQhse) {
            $rules['simper_number']   = 'required|string|unique:simpers,simper_number';
            $rules['authorized_area'] = 'required|string|max:255';
            $rules['issue_date']      = 'required|date';
            $rules['valid_until']     = 'required|date|after_or_equal:issue_date';
            $rules['equipments']      = 'required|array|min:1';
            $rules['equipments.*']    = 'string';
        }

        $request->validate($rules);

        $data = $request->all();
        $data['user_id'] = $user->id;

        if ($isQhse) {
            // Jika QHSE yang buat langsung aktif dan dianggap lolos PIC
            $data['status'] = 'Active';
            $data['pic_status'] = 'Approved';
        } else {
            // SOP Baru: Jika Pekerja yang ajukan, status PIC dan Utama adalah Pending (Menunggu Atasan)
            $data['status'] = 'Pending';
            $data['pic_status'] = 'Pending';
        }

        if ($request->hasFile('photo_path')) {
            $data['photo_path'] = $request->file('photo_path')->store('simper_photos', 'public');
        }

        Simper::create($data);

        $message = $isQhse 
            ? 'Data SIMPER berhasil ditambahkan!' 
            : 'Pengajuan SIMPER berhasil dikirim. Menunggu persetujuan PIC Dept (Atasan).';
            
        return redirect()->route('simper.index')->with('success', $message);
    }

    // 4. Menampilkan halaman detail
    public function show(Simper $simper)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['superadmin', 'qhse']) && $simper->user_id !== $user->id) {
            abort(403, 'Akses Ditolak: Anda tidak dapat melihat data milik orang lain.');
        }

        return view('simper.show', compact('simper'));
    }

    // 5. Menampilkan halaman proses / edit (QHSE)
    public function edit(Simper $simper)
    {
        if (!Auth::user()->hasAnyRole(['superadmin', 'qhse'])) {
            abort(403, 'Akses Ditolak: Hanya tim QHSE yang dapat mengedit data ini.');
        }

        return view('simper.edit', compact('simper'));
    }

    // 6. Menyimpan pembaruan data / Final Approval oleh QHSE
    public function update(Request $request, Simper $simper)
    {
        if (!Auth::user()->hasAnyRole(['superadmin', 'qhse'])) {
            abort(403, 'Akses Ditolak.');
        }

        $request->validate([
            'name'            => 'required|string|max:255',
            'nrp'             => 'nullable|string|max:255',
            'company'         => 'required|string|max:255',
            'department'      => 'required|string|max:255',
            'blood_type'      => 'required|in:A,B,AB,O',
            'position'        => 'required|string|max:255',
            'simper_number'   => 'required|string|unique:simpers,simper_number,' . $simper->id,
            'police_license'  => 'required|string|max:255',
            'authorized_area' => 'required|string|max:255',
            'issue_date'      => 'required|date',
            'valid_until'     => 'required|date|after_or_equal:issue_date',
            'equipments'      => 'required|array|min:1', 
            'equipments.*'    => 'string', 
            'photo_path'      => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status'          => 'required|in:Pending,Active,Expired,Revoked',
            'written_test_score'    => 'nullable|integer|min:0|max:100',
            'practical_test_status' => 'nullable|string',
        ]);

        $data = $request->except(['photo_path']); 

        if ($request->hasFile('photo_path')) {
            if ($simper->photo_path && Storage::disk('public')->exists($simper->photo_path)) {
                Storage::disk('public')->delete($simper->photo_path);
            }
            $data['photo_path'] = $request->file('photo_path')->store('simper_photos', 'public');
        }

        $simper->update($data);

        return redirect()->route('simper.index')->with('success', 'Data SIMPER berhasil diproses & diperbarui oleh QHSE!');
    }

    // 7. FUNGSI BARU: Aksi Approval oleh PIC Dept (Atasan)
    public function updatePicApproval(Request $request, Simper $simper)
    {
        $request->validate([
            'pic_status' => 'required|in:Approved,Rejected',
            'pic_note'   => 'nullable|string',
        ]);

        $simper->update([
            'pic_status' => $request->pic_status,
            'pic_id'     => Auth::id(), // Menyimpan siapa atasan yang meng-ACC
            'pic_note'   => $request->pic_note,
        ]);

        $statusText = $request->pic_status === 'Approved' ? 'disetujui' : 'ditolak';
        return redirect()->back()->with('success', "Pengajuan SIMPER berhasil {$statusText} oleh PIC Dept.");
    }

    // 8. Menghapus data
    public function destroy(Simper $simper)
    {
        if (!Auth::user()->hasAnyRole(['superadmin', 'qhse'])) {
            abort(403, 'Akses Ditolak.');
        }

        if ($simper->photo_path && Storage::disk('public')->exists($simper->photo_path)) {
            Storage::disk('public')->delete($simper->photo_path);
        }

        $simper->delete();

        return redirect()->route('simper.index')->with('success', 'Data SIMPER berhasil dihapus!');
    }
}