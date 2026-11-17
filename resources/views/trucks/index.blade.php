@extends('layouts.base')

@section('content')

{{-- ── Summary cards ───────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="label">Total Truk / Total Rit</div>
            <div class="value">{{ $total }} <span style="font-size:1.1rem;color:#6c757d">/ {{ $totalTrips }} Rit</span></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="label">Proses Muat</div>
            <div class="value clr-blue">{{ $statusCounts['muat'] }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="label">OTW Pelabuhan</div>
            <div class="value clr-orange">{{ $statusCounts['otw'] }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="label">Sudah BPI</div>
            <div class="value clr-green">{{ $statusCounts['bpi'] }}</div>
        </div>
    </div>
</div>

{{-- ── Toolbar ───────────────────────────────────────────── --}}
<div class="toolbar mb-3">
    <form method="GET" action="{{ route('trucks.index') }}" class="d-flex flex-wrap gap-2 align-items-center w-100">
        <input type="text" name="search" class="form-control form-control-sm"
               placeholder="Cari nopol / supir…"
               value="{{ request('search') }}"
               style="max-width:180px; min-width:110px; flex:1">

        <select name="company_id" class="form-select form-select-sm"
                style="max-width:140px; min-width:100px; flex:1"
                onchange="this.form.submit()">
            <option value="">Semua PT</option>
            @foreach($companies as $c)
                <option value="{{ $c->id }}" {{ request('company_id') == $c->id ? 'selected' : '' }}>
                    {{ $c->name }}
                </option>
            @endforeach
        </select>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-brand btn-sm">Cari</button>
            @if(request('search') || request('company_id'))
                <a href="{{ route('trucks.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            @endif
            <a href="{{ route('trucks.logs') }}" class="btn btn-outline-dark btn-sm">📜 Log Aktivitas</a>
            <a href="{{ route('trucks.create') }}" class="btn btn-brand btn-sm">+ Tambah</a>
        </div>
    </form>
</div>

{{-- ════════════════════════════════════════════════════════
     MOBILE VIEW  — kartu per truk
     ════════════════════════════════════════════════════════ --}}
<div class="d-md-none">
    @forelse($trucks as $i => $truck)
    @php
        $status = $truck->currentLoad->status     ?? 'muat';
        $pipe   = $truck->currentLoad->pipe_count ?? 0;
        $trip   = $truck->currentLoad->trip_count ?? 0;
        $wa     = 'https://wa.me/' . $truck->driver->whatsapp_number;
        $badgeClass = ['muat' => 'badge-muat', 'otw' => 'badge-otw', 'bpi' => 'badge-bpi'][$status];
        $badgeLabel = ['muat' => 'Muat', 'otw' => 'OTW Pelabuhan', 'bpi' => 'BPI'][$status];
    @endphp
    <div class="truck-card mb-3">

        {{-- Header: nopol + PT + badge rit otomatis --}}
        <div class="truck-card-header d-flex justify-content-between align-items-center">
            <div>
                <span class="truck-plate">{{ $truck->plate_number }}</span>
                <span class="badge bg-warning text-dark ms-1" style="font-size:0.75rem">🔥 {{ $trip }} Rit</span>
            </div>
            <span class="truck-pt">{{ $truck->company->name }}</span>
        </div>

        {{-- Info supir --}}
        <div class="truck-card-body">
            <div class="mb-1">
                <span class="text-muted" style="font-size:.78rem">SUPIR</span><br>
                <strong>{{ $truck->driver->name }}</strong>
            </div>
            <div>
                <span class="text-muted" style="font-size:.78rem">NO. HP</span><br>
                <a href="{{ $wa }}" target="_blank" class="wa-link">
                    📱 {{ $truck->driver->whatsapp_number }}
                </a>
            </div>
        </div>

        {{-- Inline edit: Pipa + Status (Ritase otomatis bertambah) --}}
        <form method="POST" action="{{ route('trucks.updateLoad', $truck) }}" class="truck-card-edit">
            @csrf
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <div class="d-flex align-items-center gap-1">
                    <label class="text-muted mb-0" style="font-size:.78rem">PIPA</label>
                    <input type="number" name="pipe_count" value="{{ $pipe }}" min="0"
                           class="form-control form-control-sm text-center"
                           style="width:70px" required>
                </div>
                <div class="flex-fill">
                    <select name="status" class="form-select form-select-sm" required>
                        <option value="muat" {{ $status === 'muat' ? 'selected' : '' }}>Muat</option>
                        <option value="otw"  {{ $status === 'otw'  ? 'selected' : '' }}>OTW Pelabuhan</option>
                        <option value="bpi"  {{ $status === 'bpi'  ? 'selected' : '' }}>BPI (Selesai)</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-save-inline btn-sm px-3">✓ Simpan</button>
            </div>
        </form>

        {{-- Aksi --}}
        <div class="truck-card-footer d-flex gap-2">
            <a href="{{ route('trucks.logs', ['truck_id' => $truck->id]) }}" class="btn btn-outline-secondary btn-sm flex-fill text-center">
                Lihat Log
            </a>
            <a href="{{ route('trucks.edit', $truck) }}" class="btn btn-edit btn-sm flex-fill text-center">
                Edit Lengkap
            </a>
            <form method="POST" action="{{ route('trucks.destroy', $truck) }}" style="flex:1"
                  onsubmit="return confirm('Hapus truk {{ $truck->plate_number }}?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-del btn-sm w-100">Hapus</button>
            </form>
        </div>

    </div>
    @empty
        <div class="text-center text-muted py-5">Tidak ada data truk.</div>
    @endforelse
</div>

{{-- ════════════════════════════════════════════════════════
     DESKTOP VIEW  — tabel
     ════════════════════════════════════════════════════════ --}}
<div class="d-none d-md-block">
    <div class="card-table mb-3">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Nopol</th>
                        <th>Supir</th>
                        <th>No. HP</th>
                        <th>PT</th>
                        <th class="text-center" style="width:75px">Rit</th>
                        <th class="text-center" style="width:85px">Pipe</th>
                        <th class="text-center" style="width:150px">Status</th>
                        <th class="text-center" style="width:60px">Simpan</th>
                        <th class="text-center pe-3" style="width:140px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trucks as $i => $truck)
                    @php
                        $status = $truck->currentLoad->status     ?? 'muat';
                        $pipe   = $truck->currentLoad->pipe_count ?? 0;
                        $trip   = $truck->currentLoad->trip_count ?? 0;
                        $wa     = 'https://wa.me/' . $truck->driver->whatsapp_number;
                    @endphp
                    <tr>
                        <td class="ps-3 text-muted" style="font-size:.8rem">{{ $i + 1 }}</td>
                        <td class="fw-semibold">{{ $truck->plate_number }}</td>
                        <td>{{ $truck->driver->name }}</td>
                        <td>
                            <a href="{{ $wa }}" target="_blank" class="wa-link">
                                {{ $truck->driver->whatsapp_number }}
                            </a>
                        </td>
                        <td class="text-muted" style="font-size:.88rem">{{ $truck->company->name }}</td>
                        
                        {{-- Ritase Otomatis (Tampilan Badge) --}}
                        <td class="text-center">
                            <span class="badge bg-warning text-dark fw-bold" style="font-size:0.85rem">{{ $trip }}</span>
                        </td>

                        <form method="POST" action="{{ route('trucks.updateLoad', $truck) }}" style="display:contents">
                            @csrf
                            {{-- Pipe --}}
                            <td class="text-center">
                                <input type="number" name="pipe_count" value="{{ $pipe }}" min="0"
                                       class="form-control form-control-sm text-center inline-num"
                                       style="width:70px" required>
                            </td>
                            {{-- Status --}}
                            <td class="text-center">
                                <select name="status" class="form-select form-select-sm inline-sel" required>
                                    <option value="muat" {{ $status === 'muat' ? 'selected' : '' }}>Muat</option>
                                    <option value="otw"  {{ $status === 'otw'  ? 'selected' : '' }}>OTW Pelabuhan</option>
                                    <option value="bpi"  {{ $status === 'bpi'  ? 'selected' : '' }}>BPI (Selesai)</option>
                                </select>
                            </td>
                            {{-- Tombol Simpan --}}
                            <td class="text-center">
                                <button type="submit" class="btn btn-save-inline btn-sm" title="Simpan">✓</button>
                            </td>
                        </form>

                        <td class="text-center pe-3">
                            <a href="{{ route('trucks.logs', ['truck_id' => $truck->id]) }}" class="btn btn-sm btn-outline-secondary me-1" title="Lihat History">Log</a>
                            <a href="{{ route('trucks.edit', $truck) }}" class="btn btn-edit btn-sm me-1">Edit</a>
                            <form method="POST" action="{{ route('trucks.destroy', $truck) }}" style="display:inline"
                                  onsubmit="return confirm('Hapus truk {{ $truck->plate_number }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-del btn-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">Tidak ada data truk.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-3 py-2 text-muted" style="font-size:.82rem">
            Menampilkan {{ $trucks->count() }} truk
            @if(request('search') || request('company_id')) dari hasil pencarian @endif
        </div>
    </div>
</div>

@endsection
