<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Load;
use App\Models\LoadLog;
use App\Models\Truck;
use App\Models\Driver;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TruckController extends Controller
{
    public function index(Request $request): View
    {
        // Filter Tanggal Laporan (Default Hari Ini)
        $selectedDate = $request->input('date', Carbon::today()->toDateString());

        $query = Truck::with(['driver', 'company', 'currentLoad']);

        // Search: plate number atau nama supir
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search): void {
                $q->where('plate_number', 'like', "%{$search}%")
                  ->orWhereHas('driver', fn ($d) => $d->where('name', 'like', "%{$search}%"));
            });
        }

        // Filter by company
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->input('company_id'));
        }

        // Urutkan yang baru saja diperbarui agar otomatis ke atas (order by load updated_at desc)
        $trucks = $query->leftJoin('loads', 'trucks.id', '=', 'loads.truck_id')
            ->select('trucks.*')
            ->orderByDesc('loads.updated_at')
            ->orderBy('trucks.plate_number')
            ->get();

        $companies = Company::orderBy('name')->get();

        $total        = Truck::count();
        $statusCounts = $this->buildStatusCounts($selectedDate);
        
        $totalTrips = (int) Load::sum('trip_count');

        return view('trucks.index', compact(
            'trucks',
            'companies',
            'total',
            'statusCounts',
            'totalTrips',
            'selectedDate'
        ));
    }

    public function create(): View
    {
        $companies = Company::orderBy('name')->get();
        $drivers   = Driver::orderBy('name')->get();

        return view('trucks.create', compact('companies', 'drivers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plate_number' => 'required|string|unique:trucks,plate_number',
            'driver_id'    => 'required|exists:drivers,id',
            'company_id'   => 'required|exists:companies,id',
            'pipe_count'   => 'required|integer|min:0',
            'status'       => 'required|in:bpi,muat,otw,otw_bpi',
        ]);

        $truck = Truck::create([
            'plate_number' => $validated['plate_number'],
            'driver_id'    => $validated['driver_id'],
            'company_id'   => $validated['company_id'],
        ]);

        $tripCount = $validated['status'] === 'otw_bpi' ? 1 : 0;

        Load::create([
            'truck_id'   => $truck->id,
            'pipe_count' => $validated['pipe_count'],
            'trip_count' => $tripCount,
            'status'     => $validated['status'],
        ]);

        LoadLog::create([
            'truck_id'   => $truck->id,
            'pipe_count' => $validated['pipe_count'],
            'trip_count' => $tripCount,
            'status'     => $validated['status'],
            'note'       => 'Registrasi mobil baru',
        ]);

        return redirect()->route('trucks.index')
            ->with('success', 'Truk berhasil ditambahkan.');
    }

    public function edit(Truck $truck): View
    {
        $companies = Company::orderBy('name')->get();
        $drivers   = Driver::orderBy('name')->get();
        $statuses  = ['bpi', 'muat', 'otw', 'otw_bpi'];

        return view('trucks.edit', compact('truck', 'companies', 'drivers', 'statuses'));
    }

    // Koreksi data lengkap & perbaikan jika ada salah ritase
    public function update(Request $request, Truck $truck): RedirectResponse
    {
        $validated = $request->validate([
            'plate_number' => 'required|string|unique:trucks,plate_number,' . $truck->id,
            'driver_id'    => 'required|exists:drivers,id',
            'company_id'   => 'required|exists:companies,id',
            'pipe_count'   => 'required|integer|min:0',
            'trip_count'   => 'required|integer|min:0',
            'status'       => 'required|in:bpi,muat,otw,otw_bpi',
        ]);

        $oldTrip = $truck->currentLoad ? (int) $truck->currentLoad->trip_count : 0;
        $newTrip = (int) $validated['trip_count'];

        $truck->update([
            'plate_number' => $validated['plate_number'],
            'driver_id'    => $validated['driver_id'],
            'company_id'   => $validated['company_id'],
        ]);

        $truck->currentLoad()->updateOrCreate(
            ['truck_id' => $truck->id],
            [
                'pipe_count' => $validated['pipe_count'],
                'trip_count' => $newTrip,
                'status'     => $validated['status'],
                'updated_at' => now(),
            ]
        );

        $note = 'Edit data mobil';
        if ($oldTrip !== $newTrip) {
            $note = "Koreksi Manual Ritase: {$oldTrip} → {$newTrip}";
        }

        LoadLog::create([
            'truck_id'   => $truck->id,
            'pipe_count' => $validated['pipe_count'],
            'trip_count' => $newTrip,
            'status'     => $validated['status'],
            'note'       => $note,
        ]);

        return redirect()->route('trucks.index')
            ->with('success', "Data {$truck->plate_number} berhasil diperbarui.");
    }

    // Inline update – Pipa & Status (Alur: BPI -> Muat -> OTW Pelabuhan -> OTW BPI)
    public function updateLoad(Request $request, Truck $truck): RedirectResponse
    {
        $validated = $request->validate([
            'pipe_count' => 'required|integer|min:0',
            'status'     => 'required|in:bpi,muat,otw,otw_bpi',
        ]);

        $oldLoad = $truck->currentLoad;
        $currentTrip = $oldLoad ? (int) $oldLoad->trip_count : 0;
        $notes = [];

        // Logika Otomatis Sesuai Alur:
        // Saat mobil berangkat membawa muatan menuju BPI (status berubah jadi 'otw_bpi'), ritase otomatis naik +1
        $ritTambah = false;
        if ($oldLoad && $oldLoad->status !== 'otw_bpi' && $validated['status'] === 'otw_bpi') {
            $currentTrip++;
            $ritTambah = true;
            $notes[] = "Berangkat OTW BPI: Ritase otomatis bertambah jadi {$currentTrip}";
        }

        if ($oldLoad) {
            if ($oldLoad->status !== $validated['status']) {
                $statusLabels = [
                    'bpi'     => 'BPI',
                    'muat'    => 'Proses Muat',
                    'otw'     => 'OTW Pelabuhan',
                    'otw_bpi' => 'OTW BPI',
                ];
                $oldTxt = $statusLabels[$oldLoad->status] ?? $oldLoad->status;
                $newTxt = $statusLabels[$validated['status']] ?? $validated['status'];
                $notes[] = "Status: {$oldTxt} → {$newTxt}";
            }
            if ($oldLoad->pipe_count != $validated['pipe_count']) {
                $notes[] = "Pipa: {$oldLoad->pipe_count} → {$validated['pipe_count']}";
            }
        }

        $note = !empty($notes) ? implode(' | ', $notes) : 'Update status & pipa';

        $truck->currentLoad()->updateOrCreate(
            ['truck_id' => $truck->id],
            [
                'pipe_count' => $validated['pipe_count'],
                'trip_count' => $currentTrip,
                'status'     => $validated['status'],
                'updated_at' => now(), // Memastikan updated_at berubah agar langsung naik ke urutan paling atas
            ]
        );

        LoadLog::create([
            'truck_id'   => $truck->id,
            'pipe_count' => $validated['pipe_count'],
            'trip_count' => $currentTrip,
            'status'     => $validated['status'],
            'note'       => $note,
        ]);

        $msg = "Status {$truck->plate_number} berhasil diperbarui.";
        if ($ritTambah) {
            $msg .= " (Ritase otomatis naik jadi {$currentTrip} Rit)";
        }

        return redirect()->route('trucks.index', [
            'search'     => $request->input('search'),
            'company_id' => $request->input('company_id'),
            'date'       => $request->input('date', now()->toDateString())
        ])->with('success', $msg);
    }

    // Tombol Cepat: Tambah 1 Rit Langsung
    public function incrementTrip(Request $request, Truck $truck): RedirectResponse
    {
        $currentLoad = $truck->currentLoad;
        $newTrip = $currentLoad ? ((int) $currentLoad->trip_count + 1) : 1;

        $truck->currentLoad()->updateOrCreate(
            ['truck_id' => $truck->id],
            [
                'trip_count' => $newTrip,
                'updated_at' => now(),
            ]
        );

        LoadLog::create([
            'truck_id'   => $truck->id,
            'pipe_count' => $currentLoad->pipe_count ?? 0,
            'trip_count' => $newTrip,
            'status'     => $currentLoad->status ?? 'bpi',
            'note'       => "Tambah cepat ritase: {$newTrip} Rit",
        ]);

        return redirect()->back()->with('success', "Ritase {$truck->plate_number} bertambah jadi {$newTrip} Rit.");
    }

    // Fitur Cetak / Print Laporan
    public function print(Request $request): View
    {
        $selectedDate = $request->input('date', Carbon::today()->toDateString());
        $companyId    = $request->input('company_id');

        $query = Truck::with(['driver', 'company', 'currentLoad']);

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        $trucks = $query->leftJoin('loads', 'trucks.id', '=', 'loads.truck_id')
            ->select('trucks.*')
            ->orderBy('trucks.company_id')
            ->orderBy('trucks.plate_number')
            ->get();

        $companyName  = $companyId ? Company::find($companyId)?->name : 'Semua PT';
        $statusCounts = $this->buildStatusCounts($selectedDate);
        $totalPipes   = (int) Load::sum('pipe_count');
        $totalTrips   = (int) Load::sum('trip_count');

        return view('trucks.print', compact(
            'trucks',
            'selectedDate',
            'companyName',
            'statusCounts',
            'totalPipes',
            'totalTrips'
        ));
    }

    // Halaman History Log Aktivitas
    public function logs(Request $request): View
    {
        $query = LoadLog::with(['truck.driver', 'truck.company'])->latest();

        if ($request->filled('truck_id')) {
            $query->where('truck_id', $request->input('truck_id'));
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }

        $logs = $query->paginate(30)->withQueryString();
        $trucks = Truck::orderBy('plate_number')->get();

        return view('trucks.logs', compact('logs', 'trucks'));
    }

    // Hapus satu baris log tertentu jika salah
    public function destroyLog(LoadLog $log): RedirectResponse
    {
        $log->delete();
        return redirect()->back()->with('success', 'Catatan log berhasil dihapus.');
    }

    public function destroy(Truck $truck): RedirectResponse
    {
        $truck->delete();

        return redirect()->route('trucks.index')
            ->with('success', 'Truk berhasil dihapus.');
    }

    // ── Private helpers ───────────────────────────────────────

    private function buildStatusCounts(string $date): array
    {
        $raw = Load::selectRaw('status, count(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status')
            ->all();

        return array_merge([
            'bpi'     => 0,
            'muat'    => 0,
            'otw'     => 0,
            'otw_bpi' => 0,
        ], $raw);
    }
}
