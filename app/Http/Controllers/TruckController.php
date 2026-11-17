<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Load;
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

        return view('trucks.index', compact('trucks', 'companies', 'total', 'statusCounts'));
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

        Load::create([
            'truck_id'   => $truck->id,
            'pipe_count' => $validated['pipe_count'],
            'status'     => $validated['status'],
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

    public function update(Request $request, Truck $truck): RedirectResponse
    {
        $validated = $request->validate([
            'plate_number' => 'required|string|unique:trucks,plate_number,' . $truck->id,
            'driver_id'    => 'required|exists:drivers,id',
            'company_id'   => 'required|exists:companies,id',
            'pipe_count'   => 'required|integer|min:0',
            'status'       => 'required|in:muat,otw,bpi',
        ]);

        $truck->update([
            'plate_number' => $validated['plate_number'],
            'driver_id'    => $validated['driver_id'],
            'company_id'   => $validated['company_id'],
        ]);

        $truck->currentLoad()->updateOrCreate(
            ['truck_id' => $truck->id],
            ['pipe_count' => $validated['pipe_count'], 'status' => $validated['status']]
        );

        return redirect()->route('trucks.index')
            ->with('success', 'Data truk berhasil diperbarui.');
    }

    // Inline update – hanya pipe_count & status (dari modal di dashboard)
    public function updateLoad(Request $request, Truck $truck): RedirectResponse
    {
        $validated = $request->validate([
            'pipe_count' => 'required|integer|min:0',
            'status'     => 'required|in:muat,otw,bpi',
        ]);

        $truck->currentLoad()->updateOrCreate(
            ['truck_id' => $truck->id],
            $validated
        );

        return redirect()->route('trucks.index')
            ->with('success', "Status {$truck->plate_number} diperbarui.");
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
