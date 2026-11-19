@extends('layouts.base')

@section('content')

{{-- ── Summary cards (Urutan: BPI -> Proses Muat -> OTW Pelabuhan -> OTW BPI) ── --}}
<div class="row g-2 mb-3">
    <div class="col-6 col-md">
        <div class="stat-card py-2 px-3">
            <div class="label" style="font-size:0.75rem">Total Armada / Total Rit</div>
            <div class="value" style="font-size:1.4rem">{{ $total }} <span style="font-size:0.9rem;color:#6c757d">/ {{ $totalTrips }} Rit</span></div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card py-2 px-3">
            <div class="label" style="font-size:0.75rem">1. BPI</div>
            <div class="value clr-green" style="font-size:1.4rem">{{ $statusCounts['bpi'] ?? 0 }}</div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card py-2 px-3">
            <div class="label" style="font-size:0.75rem">2. Proses Muat</div>
            <div class="value clr-blue" style="font-size:1.4rem">{{ $statusCounts['muat'] ?? 0 }}</div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card py-2 px-3">
            <div class="label" style="font-size:0.75rem">3. OTW Pelabuhan</div>
            <div class="value clr-orange" style="font-size:1.4rem">{{ $statusCounts['otw'] ?? 0 }}</div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card py-2 px-3">
            <div class="label" style="font-size:0.75rem">4. OTW BPI</div>
            <div class="value clr-yellow" style="font-size:1.4rem">{{ $statusCounts['otw_bpi'] ?? 0 }}</div>
        </div>
    </div>
</div>

{{-- ── Toolbar: Filter Tanggal, Search, Filter PT, Cetak & Tambah ────────────────── --}}
<div class="toolbar mb-3 p-2 bg-white border rounded">
    <form method="GET" action="{{ route('trucks.index') }}" class="d-flex flex-wrap gap-2 align-items-center w-100">
        {{-- Filter Tanggal Laporan --}}
        <div class="d-flex align-items-center gap-1">
            <span class="text-muted" style="font-size:0.8rem">📅</span>
            <input type="date" name="date" class="form-control form-control-sm"
                   value="{{ $selectedDate }}" onchange="this.form.submit()"
                   title="Pilih Tanggal Laporan" style="max-width:140px">
        </div>

        {{-- Search Nopol / Supir --}}
        <input type="text" name="search" class="form-control form-control-sm"
               placeholder="Cari nopol / supir…"
               value="{{ request('search') }}"
               style="max-width:170px; min-width:110px; flex:1">

        {{-- Filter PT Tracking --}}
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

        <div class="d-flex gap-1 ms-auto flex-wrap">
            <button type="submit" class="btn btn-brand btn-sm">Cari</button>
            @if(request('search') || request('company_id') || request('date'))
                <a href="{{ route('trucks.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            @endif

            {{-- Tombol Cetak / Print --}}
            <a href="{{ route('trucks.print', ['date' => $selectedDate, 'company_id' => request('company_id')]) }}" 
               target="_blank" class="btn btn-success btn-sm" title="Cetak Laporan PDF / Print">
                🖨️ Cetak
            </a>

            <a href="{{ route('trucks.logs') }}" class="btn btn-outline-dark btn-sm">📜 Log</a>
            <a href="{{ route('trucks.create') }}" class="btn btn-brand btn-sm">+ Tambah</a>
        </div>
    </form>
</div>

{{-- ════════════════════════════════════════════════════════
     MOBILE VIEW  — kartu per truk (Urut: Baru diperbarui paling atas)
     ════════════════════════════════════════════════════════ --}}
<div class="d-md-none">
    @forelse($trucks as $i => $truck)
    @php
        $status = $truck->currentLoad->status     ?? 'bpi';
        $pipe   = $truck->currentLoad->pipe_count ?? 0;
        $trip   = $truck->currentLoad->trip_count ?? 0;
        $wa     = 'https://wa.me/' . $truck->driver->whatsapp_number;
        $updatedAt = $truck->currentLoad->updated_at ? $truck->currentLoad->updated_at->diffForHumans() : '-';
    @endphp
    <div class="truck-card mb-3">

        {{-- Header: nopol + PT + badge rit + tombol quick +1 rit --}}
        <div class="truck-card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-1">
                <span class="truck-plate">{{ $truck->plate_number }}</span>
                <span class="badge bg-warning text-dark ms-1" style="font-size:0.75rem">🔥 {{ $trip }} Rit</span>
                
                {{-- Tombol Cepat Tambah 1 Rit --}}
                <form method="POST" action="{{ route('trucks.incrementTrip', $truck) }}" style="display:inline"
                      onsubmit="return confirm('Tambah 1 Rit untuk {{ $truck->plate_number }}?')">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-light py-0 px-1 text-dark fw-bold border" 
                            style="font-size:0.7rem;line-height:1.2" title="Klik untuk tambah 1 rit langsung">+1</button>
                </form>
            </div>
            <span class="truck-pt">{{ $truck->company->name }}</span>
        </div>

        {{-- Info supir & waktu update --}}
        <div class="truck-card-body">
            <div class="mb-1">
                <span class="text-muted" style="font-size:.75rem">SUPIR</span><br>
                <strong>{{ $truck->driver->name }}</strong>
            </div>
            <div>
                <span class="text-muted" style="font-size:.75rem">NO. HP</span><br>
                <a href="{{ $wa }}" target="_blank" class="wa-link">
                    📱 {{ $truck->driver->whatsapp_number }}
                </a>
            </div>
            <div class="ms-auto text-end">
                <span class="text-muted" style="font-size:.7rem">UPDATE</span><br>
                <span class="badge bg-light text-dark border" style="font-size:0.7rem">{{ $updatedAt }}</span>
            </div>
        </div>

        {{-- Inline edit: Pipa + Status (Urutan: BPI -> Muat -> OTW Pelabuhan -> OTW BPI) --}}
        <form method="POST" action="{{ route('trucks.updateLoad', $truck) }}" class="truck-card-edit">
            @csrf
            <input type="hidden" name="date" value="{{ $selectedDate }}">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="company_id" value="{{ request('company_id') }}">

            <div class="d-flex gap-2 align-items-center flex-wrap">
                <div class="d-flex align-items-center gap-1">
                    <label class="text-muted mb-0" style="font-size:.75rem">PIPA</label>
                    <input type="number" name="pipe_count" value="{{ $pipe }}" min="0"
                           class="form-control form-control-sm text-center"
                           style="width:65px" required>
                </div>
                <div class="flex-fill">
                    <select name="status" class="form-select form-select-sm" required>
                        <option value="bpi"     {{ $status === 'bpi' ? 'selected' : '' }}>1. BPI</option>
                        <option value="muat"    {{ $status === 'muat' ? 'selected' : '' }}>2. Proses Muat</option>
                        <option value="otw"     {{ $status === 'otw' ? 'selected' : '' }}>3. OTW Pelabuhan</option>
                        <option value="otw_bpi" {{ $status === 'otw_bpi' ? 'selected' : '' }}>4. OTW BPI (+1 Rit)</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-save-inline btn-sm px-3">✓ Simpan</button>
            </div>
        </form>

        {{-- Aksi --}}
        <div class="truck-card-footer d-flex gap-2">
            <a href="{{ route('trucks.logs', ['truck_id' => $truck->id]) }}" class="btn btn-outline-secondary btn-sm flex-fill text-center">
                Log
            </a>
            <a href="{{ route('trucks.edit', $truck) }}" class="btn btn-edit btn-sm flex-fill text-center">
                Edit
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
     DESKTOP VIEW  — tabel (Urut: Baru diperbarui paling atas)
     ════════════════════════════════════════════════════════ --}}
<div class="d-none d-md-block">
    <div class="card-table mb-3">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3" style="width:40px">#</th>
                        <th>Nopol</th>
                        <th>Supir</th>
                        <th>No. HP</th>
                        <th>PT</th>
                        <th class="text-center" style="width:90px">Rit</th>
                        <th class="text-center" style="width:80px">Pipe</th>
                        <th class="text-center" style="width:160px">Status</th>
                        <th class="text-center" style="width:50px">Simpan</th>
                        <th class="text-center" style="width:100px">Diperbarui</th>
                        <th class="text-center pe-3" style="width:130px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trucks as $i => $truck)
                    @php
                        $status = $truck->currentLoad->status     ?? 'bpi';
                        $pipe   = $truck->currentLoad->pipe_count ?? 0;
                        $trip   = $truck->currentLoad->trip_count ?? 0;
                        $wa     = 'https://wa.me/' . $truck->driver->whatsapp_number;
                        $updatedAt = $truck->currentLoad->updated_at ? $truck->currentLoad->updated_at->diffForHumans() : '-';
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
                        <td class="text-muted" style="font-size:.85rem">{{ $truck->company->name }}</td>
                        
                        {{-- Ritase Otomatis + Tombol Cepat +1 --}}
                        <td class="text-center">
                            <span class="badge bg-warning text-dark fw-bold me-1">{{ $trip }}</span>
                            <form method="POST" action="{{ route('trucks.incrementTrip', $truck) }}" style="display:inline"
                                  onsubmit="return confirm('Tambah 1 Rit untuk {{ $truck->plate_number }}?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary py-0 px-1 border" 
                                        style="font-size:0.65rem;line-height:1" title="Tambah 1 rit langsung">+1</button>
                            </form>
                        </td>

                        <form method="POST" action="{{ route('trucks.updateLoad', $truck) }}" style="display:contents">
                            @csrf
                            <input type="hidden" name="date" value="{{ $selectedDate }}">
                            <input type="hidden" name="search" value="{{ request('search') }}">
                            <input type="hidden" name="company_id" value="{{ request('company_id') }}">

                            {{-- Pipe --}}
                            <td class="text-center">
                                <input type="number" name="pipe_count" value="{{ $pipe }}" min="0"
                                       class="form-control form-control-sm text-center inline-num"
                                       style="width:65px" required>
                            </td>
                            {{-- Status Urutan: BPI -> Proses Muat -> OTW Pelabuhan -> OTW BPI --}}
                            <td class="text-center">
                                <select name="status" class="form-select form-select-sm inline-sel" required>
                                    <option value="bpi"     {{ $status === 'bpi' ? 'selected' : '' }}>1. BPI</option>
                                    <option value="muat"    {{ $status === 'muat' ? 'selected' : '' }}>2. Proses Muat</option>
                                    <option value="otw"     {{ $status === 'otw' ? 'selected' : '' }}>3. OTW Pelabuhan</option>
                                    <option value="otw_bpi" {{ $status === 'otw_bpi' ? 'selected' : '' }}>4. OTW BPI (+1 Rit)</option>
                                </select>
                            </td>
                            {{-- Tombol Simpan --}}
                            <td class="text-center">
                                <button type="submit" class="btn btn-save-inline btn-sm" title="Simpan">✓</button>
                            </td>
                        </form>

                        {{-- Waktu terakhir update --}}
                        <td class="text-center text-muted" style="font-size:0.75rem">
                            {{ $updatedAt }}
                        </td>

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
                        <td colspan="11" class="text-center text-muted py-4">Tidak ada data truk.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-3 py-2 text-muted" style="font-size:.82rem">
            Menampilkan {{ $trucks->count() }} truk (Urutan teratas: baru saja di-update)
        </div>
    </div>
</div>

@endsection
