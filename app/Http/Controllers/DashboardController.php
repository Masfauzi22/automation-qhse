<?php

namespace App\Http\Controllers;

use App\Models\WorkPermit;
use App\Support\HazardSheetDashboard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, HazardSheetDashboard $sheet): View|RedirectResponse
    {
        // ?refresh=1 → ambil ulang dari Google Sheets sekarang (khusus superadmin & QHSE),
        // lalu kembali ke /dashboard supaya refresh browser tidak mengulang permintaan ini
        if ($request->boolean('refresh')) {
            if ($request->user()?->hasAnyRole(['superadmin', 'qhse'])) {
                $sheet->get(refresh: true);
            }

            return redirect()->route('dashboard');
        }

        return view('dashboard', [
            'hazard'        => $sheet->get(),
            // Permit to Work yang sedang berlaku (dari database AQHSE)
            'activePermits' => WorkPermit::where('status', WorkPermit::STATUS_ACTIVE)->count(),
        ]);
    }
}