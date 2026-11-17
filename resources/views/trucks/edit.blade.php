@extends('layouts.base')

@section('content')

<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('trucks.index') }}" class="text-decoration-none text-muted" style="font-size:.9rem">← Kembali</a>
</div>

<div class="section-title">Edit Data Truk – {{ $truck->plate_number }}</div>

<div class="form-card" style="max-width:540px">
    <form method="POST" action="{{ route('trucks.update', $truck) }}">
        @csrf @method('PUT')

        <div class="mb-3">
            <label class="form-label">Nomor Polisi</label>
            <input type="text" name="plate_number" class="form-control @error('plate_number') is-invalid @enderror"
                   value="{{ old('plate_number', $truck->plate_number) }}" required>
            @error('plate_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Supir</label>
            <select name="driver_id" class="form-select @error('driver_id') is-invalid @enderror" required>
                @foreach($drivers as $d)
                    <option value="{{ $d->id }}" {{ (old('driver_id', $truck->driver_id) == $d->id) ? 'selected' : '' }}>
                        {{ $d->name }} – {{ $d->phone }}
                    </option>
                @endforeach
            </select>
            @error('driver_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label">PT Tracking</label>
            <select name="company_id" class="form-select @error('company_id') is-invalid @enderror" required>
                @foreach($companies as $c)
                    <option value="{{ $c->id }}" {{ (old('company_id', $truck->company_id) == $c->id) ? 'selected' : '' }}>
                        {{ $c->name }}
                    </option>
                @endforeach
            </select>
            @error('company_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- Koreksi Angka Ritase & Pipa --}}
        <div class="row g-3 mb-3">
            <div class="col-6">
                <label class="form-label text-primary fw-bold">Koreksi Ritase (Total Rit)</label>
                <input type="number" name="trip_count" class="form-control @error('trip_count') is-invalid @enderror"
                       min="0" value="{{ old('trip_count', $truck->currentLoad->trip_count ?? 0) }}" required>
                <small class="text-muted" style="font-size:0.75rem">Ubah di sini jika ada salah klik status.</small>
                @error('trip_count')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-6">
                <label class="form-label">Jumlah Pipe</label>
                <input type="number" name="pipe_count" class="form-control @error('pipe_count') is-invalid @enderror"
                       min="0" value="{{ old('pipe_count', $truck->currentLoad->pipe_count ?? 0) }}" required>
                @error('pipe_count')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Status Saat Ini</label>
            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                @php $currentStatus = old('status', $truck->currentLoad->status ?? 'bpi'); @endphp
                <option value="muat" {{ $currentStatus == 'muat' ? 'selected' : '' }}>Proses Muat</option>
                <option value="otw"  {{ $currentStatus == 'otw'  ? 'selected' : '' }}>OTW Pelabuhan</option>
                <option value="bpi"  {{ $currentStatus == 'bpi'  ? 'selected' : '' }}>BPI (Selesai Bongkar)</option>
            </select>
            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-brand px-4">Simpan Perubahan</button>
            <a href="{{ route('trucks.index') }}" class="btn btn-outline-secondary px-4">Batal</a>
        </div>
    </form>
</div>

@endsection
