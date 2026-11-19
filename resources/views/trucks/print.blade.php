<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Monitoring Truk Pipa - {{ $selectedDate }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header h2 {
            margin: 0 0 5px 0;
            font-size: 18px;
            text-transform: uppercase;
        }
        .header p {
            margin: 0;
            font-size: 12px;
            color: #555;
        }
        .meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 11px;
        }
        .summary-box {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        .summary-item {
            flex: 1;
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
            border-radius: 4px;
        }
        .summary-item .title {
            font-size: 10px;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 3px;
        }
        .summary-item .count {
            font-size: 16px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #666;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-size: 11px;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-muat { background-color: #dbeafe; color: #1e40af; }
        .badge-otw { background-color: #fef3c7; color: #92400e; }
        .badge-otw_bpi { background-color: #fef9c3; color: #854d0e; }
        .badge-bpi { background-color: #dcfce7; color: #166534; }
        .footer {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        .signature {
            text-align: center;
            width: 180px;
        }
        .signature .line {
            margin-top: 50px;
            border-bottom: 1px solid #333;
        }
        @media print {
            .no-print { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; font-weight: bold; background: #2563eb; color: #fff; border: none; border-radius: 4px; cursor: pointer;">
            🖨️ Cetak / Simpan PDF
        </button>
        <button onclick="window.close()" style="padding: 8px 16px; background: #6b7280; color: #fff; border: none; border-radius: 4px; cursor: pointer; margin-left: 5px;">
            Tutup
        </button>
    </div>

    <div class="header">
        <h2>LAPORAN MONITORING PENGANGKUTAN PIPA</h2>
        <p>Proyek Pengiriman Pipa ke BPI (Shipment #3)</p>
    </div>

    <div class="meta">
        <div>
            <strong>Tanggal Laporan:</strong> {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d F Y') }}<br>
            <strong>Filter PT:</strong> {{ $companyName }}
        </div>
        <div style="text-align: right;">
            <strong>Dicetak pada:</strong> {{ now()->format('d/m/Y H:i') }} WIB<br>
            <strong>Total Armada:</strong> {{ $trucks->count() }} Unit Mobil
        </div>
    </div>

    {{-- Ringkasan --}}
    <div class="summary-box">
        <div class="summary-item">
            <div class="title">Total Ritase</div>
            <div class="count">{{ $totalTrips }} Rit</div>
        </div>
        <div class="summary-item">
            <div class="title">Total Pipa Aktif</div>
            <div class="count">{{ $totalPipes }} Pipa</div>
        </div>
        <div class="summary-item">
            <div class="title">Muat</div>
            <div class="count" style="color:#2563eb">{{ $statusCounts['muat'] ?? 0 }}</div>
        </div>
        <div class="summary-item">
            <div class="title">OTW Pelabuhan</div>
            <div class="count" style="color:#d97706">{{ $statusCounts['otw'] ?? 0 }}</div>
        </div>
        <div class="summary-item">
            <div class="title">OTW BPI</div>
            <div class="count" style="color:#ca8a04">{{ $statusCounts['otw_bpi'] ?? 0 }}</div>
        </div>
        <div class="summary-item">
            <div class="title">BPI</div>
            <div class="count" style="color:#16a34a">{{ $statusCounts['bpi'] ?? 0 }}</div>
        </div>
    </div>

    {{-- Tabel Data --}}
    <table>
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th style="width: 90px;">No. Polisi</th>
                <th>Nama Supir</th>
                <th>No. Telepon</th>
                <th>PT Tracking</th>
                <th class="text-center" style="width: 50px;">Rit</th>
                <th class="text-center" style="width: 50px;">Pipa</th>
                <th class="text-center" style="width: 100px;">Status Terkini</th>
            </tr>
        </thead>
        <tbody>
            @forelse($trucks as $i => $truck)
            @php
                $status = $truck->currentLoad->status ?? 'bpi';
                $pipe   = $truck->currentLoad->pipe_count ?? 0;
                $trip   = $truck->currentLoad->trip_count ?? 0;
                $statusLabels = [
                    'muat'    => 'Muat',
                    'otw'     => 'OTW Pelabuhan',
                    'otw_bpi' => 'OTW BPI',
                    'bpi'     => 'BPI'
                ];
            @endphp
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td><strong>{{ $truck->plate_number }}</strong></td>
                <td>{{ $truck->driver->name }}</td>
                <td>{{ $truck->driver->phone }}</td>
                <td>{{ $truck->company->name }}</td>
                <td class="text-center">{{ $trip }}</td>
                <td class="text-center">{{ $pipe }}</td>
                <td class="text-center">
                    <span class="badge badge-{{ $status }}">
                        {{ $statusLabels[$status] ?? $status }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">Tidak ada data untuk ditampilkan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div class="signature">
            <p>Dibuat Oleh,</p>
            <div class="line"></div>
            <p>Petugas Lapangan / Checker</p>
        </div>
        <div class="signature">
            <p>Mengetahui,</p>
            <div class="line"></div>
            <p>Koordinator Logistik</p>
        </div>
    </div>

</body>
</html>
