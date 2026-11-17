@extends('layouts.base')

@section('content')

<div class="d-flex align-items-center justify-content-between mb-3">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('trucks.index') }}" class="text-decoration-none text-muted" style="font-size:.9rem">← Kembali ke Dashboard</a>
    </div>
</div>

<div class="section-title">📜 Log Aktivitas & Perubahan Status Truk</div>

{{-- Filter log per mobil --}}
<div class="card p-3 mb-4 border-0 shadow-sm" style="background:#fff;border-radius:8px">
    <form method="GET" action="{{ route('trucks.logs') }}" class="d-flex flex-wrap gap-2 align-items-center">
        <label class="form-label mb-0 fw-semibold" style="font-size:.9rem">Filter Mobil:</label>
        <select name="truck_id" class="form-select form-select-sm" style="max-width:240px" onchange="this.form.submit()">
            <option value="">Semua Mobil</option>
            @foreach($trucks as $t)
                <option value="{{ $t->id }}" {{ request('truck_id') == $t->id ? 'selected' : '' }}>
                    {{ $t->plate_number }} - {{ $t->driver->name ?? '' }} ({{ $t->company->name ?? '' }})
                </option>
            @endforeach
        </select>
        @if(request('truck_id'))
            <a href="{{ route('trucks.logs') }}" class="btn btn-sm btn-outline-secondary">Reset Filter</a>
        @endif
    </form>
</div>

<div class="card-table mb-4">
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-3" style="width:150px">Waktu</th>
                    <th>Nopol Mobil</th>
                    <th>Supir</th>
                    <th>PT Tracking</th>
                    <th class="text-center">Rit Ke-</th>
                    <th class="text-center">Pipa</th>
                    <th class="text-center">Status</th>
                    <th>Keterangan Perubahan</th>
                    <th class="text-center pe-3" style="width:70px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                @php
                    $status = $log->status;
                    $label  = ['muat' => 'Muat', 'otw' => 'OTW Pelabuhan', 'bpi' => 'BPI'][$status] ?? $status;
                @endphp
                <tr>
                    <td class="ps-3 text-muted" style="font-size:.82rem">
                        {{ $log->created_at ? $log->created_at->format('d M Y, H:i') : '-' }}
                    </td>
                    <td class="fw-semibold">{{ $log->truck->plate_number ?? '-' }}</td>
                    <td>{{ $log->truck->driver->name ?? '-' }}</td>
                    <td class="text-muted">{{ $log->truck->company->name ?? '-' }}</td>
                    <td class="text-center">
                        <span class="badge bg-warning text-dark">Rit {{ $log->trip_count }}</span>
                    </td>
                    <td class="text-center fw-semibold">{{ $log->pipe_count }}</td>
                    <td class="text-center">
                        <span class="badge badge-{{ $status }} rounded-pill px-2">{{ $label }}</span>
                    </td>
                    <td class="text-muted" style="font-size:.85rem">
                        {{ $log->note ?? '-' }}
                    </td>
                    <td class="text-center pe-3">
                        <form method="POST" action="{{ route('trucks.logs.destroy', $log) }}" style="display:inline"
                              onsubmit="return confirm('Hapus baris log ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-del btn-sm" title="Hapus riwayat yang salah">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">Belum ada riwayat aktivitas log.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-end">
    {{ $logs->links() }}
</div>

@endsection
