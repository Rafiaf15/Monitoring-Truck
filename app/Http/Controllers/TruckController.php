<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Load;
use App\Models\LoadLog;
use App\Models\Truck;
use App\Models\Driver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TruckController extends Controller
{
    public function index(Request $request): View
    {
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

        $trucks    = $query->orderBy('company_id')->get();
        $companies = Company::orderBy('name')->get();

        $total        = Truck::count();
        $statusCounts = $this->buildStatusCounts();
        $totalTrips   = (int) Load::sum('trip_count');

        return view('trucks.index', compact('trucks', 'companies', 'total', 'statusCounts', 'totalTrips'));
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
            'status'       => 'required|in:muat,otw,bpi',
        ]);

        $truck = Truck::create([
            'plate_number' => $validated['plate_number'],
            'driver_id'    => $validated['driver_id'],
            'company_id'   => $validated['company_id'],
        ]);

        $tripCount = $validated['status'] === 'bpi' ? 1 : 0;

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
        $statuses  = ['muat', 'otw', 'bpi'];

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
            'status'       => 'required|in:muat,otw,bpi',
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
                'status'     => $validated['status']
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

    // Inline update – Pipa & Status
    public function updateLoad(Request $request, Truck $truck): RedirectResponse
    {
        $validated = $request->validate([
            'pipe_count' => 'required|integer|min:0',
            'status'     => 'required|in:muat,otw,bpi',
        ]);

        $oldLoad = $truck->currentLoad;
        $currentTrip = $oldLoad ? (int) $oldLoad->trip_count : 0;
        $notes = [];

        // Logika Otomatis: Jika status berubah menjadi BPI (selesai bongkar/sampai), ritase otomatis +1
        $ritTambah = false;
        if ($oldLoad && $oldLoad->status !== 'bpi' && $validated['status'] === 'bpi') {
            $currentTrip++;
            $ritTambah = true;
            $notes[] = "Selesai bongkar (BPI): Ritase otomatis naik jadi {$currentTrip}";
        }

        if ($oldLoad) {
            if ($oldLoad->status !== $validated['status']) {
                $notes[] = "Status: {$oldLoad->status} → {$validated['status']}";
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
            $msg .= " (Ritase naik jadi {$currentTrip})";
        }

        return redirect()->route('trucks.index')->with('success', $msg);
    }

    // Halaman History Log Aktivitas
    public function logs(Request $request): View
    {
        $query = LoadLog::with(['truck.driver', 'truck.company'])->latest();

        if ($request->filled('truck_id')) {
            $query->where('truck_id', $request->input('truck_id'));
        }

        $logs = $query->paginate(25);
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

    private function buildStatusCounts(): array
    {
        $raw = Load::selectRaw('status, count(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status')
            ->all();

        return array_merge(['muat' => 0, 'otw' => 0, 'bpi' => 0], $raw);
    }
}
