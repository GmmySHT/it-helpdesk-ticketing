{{--
    File: resources/views/reports/laporan-pdf.blade.php
    Fungsi: Template PDF untuk laporan tiket dengan SLA dan Priority
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title ?? 'Laporan Tiket' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 9px;
            line-height: 1.3;
            color: #333;
            padding: 15px;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }

        .header h1 {
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .header h3 {
            font-size: 11px;
            color: #7f8c8d;
            font-weight: normal;
        }

        /* Info Section */
        .info-section {
            margin-bottom: 15px;
            padding: 8px;
            background-color: #f8f9fa;
            border-left: 3px solid #3498db;
            font-size: 9px;
        }

        .info-row {
            margin-bottom: 3px;
        }

        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 100px;
        }

        .info-value {
            display: inline-block;
        }

        /* Table Styles */
        .table-container {
            margin: 15px 0;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }

        th {
            background-color: #3498db;
            color: white;
            font-weight: bold;
            padding: 6px 4px;
            text-align: center;
            border: 1px solid #2980b9;
            font-size: 9px;
        }

        td {
            padding: 5px 4px;
            border: 1px solid #ddd;
            text-align: left;
            vertical-align: top;
        }

        .text-center {
            text-align: center;
        }

        /* Priority Styles */
        .priority-high {
            background-color: #e74c3c;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            display: inline-block;
            font-weight: bold;
            font-size: 8px;
        }

        .priority-medium {
            background-color: #f39c12;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            display: inline-block;
            font-weight: bold;
            font-size: 8px;
        }

        .priority-low {
            background-color: #27ae60;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            display: inline-block;
            font-weight: bold;
            font-size: 8px;
        }

        .priority-critical {
            background-color: #8e44ad;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            display: inline-block;
            font-weight: bold;
            font-size: 8px;
        }

        /* Status Styles */
        .status-open {
            color: #e74c3c;
            font-weight: bold;
        }

        .status-progress {
            color: #f39c12;
            font-weight: bold;
        }

        .status-resolved {
            color: #27ae60;
            font-weight: bold;
        }

        .status-closed {
            color: #95a5a6;
            font-weight: bold;
        }

        /* SLA Status */
        .sla-overdue {
            background-color: #e74c3c;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            display: inline-block;
            font-size: 7px;
            font-weight: bold;
        }

        .sla-warning {
            background-color: #f39c12;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            display: inline-block;
            font-size: 7px;
            font-weight: bold;
        }

        .sla-ontime {
            background-color: #27ae60;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            display: inline-block;
            font-size: 7px;
            font-weight: bold;
        }

        .sla-default {
            background-color: #95a5a6;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            display: inline-block;
            font-size: 7px;
            font-weight: bold;
        }

        /* Footer */
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 8px;
            color: #7f8c8d;
        }

        .signature {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            width: 200px;
            text-align: center;
        }

        .signature-line {
            margin-top: 40px;
            padding-top: 5px;
            border-top: 1px solid #333;
        }

        @media print {
            body {
                padding: 0;
                margin: 0;
            }

            th {
                background-color: #3498db !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .priority-high, .priority-medium, .priority-low, .priority-critical,
            .sla-overdue, .sla-warning, .sla-ontime {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>{{ $title ?? 'LAPORAN TIKET' }}</h1>
        <h3>{{ $subtitle ?? 'Sistem Manajemen Tiket' }}</h3>
    </div>

    {{-- Informasi Laporan --}}
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Tanggal Cetak:</span>
            <span class="info-value">{{ $date->format('d/m/Y H:i:s') ?? now()->format('d/m/Y H:i:s') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Periode Laporan:</span>
            <span class="info-value">
                @if(isset($start_date) && isset($end_date))
                    {{ date('d/m/Y', strtotime($start_date)) }} - {{ date('d/m/Y', strtotime($end_date)) }}
                @else
                    Semua Data
                @endif
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Total Tiket:</span>
            <span class="info-value">{{ count($data) }} tiket</span>
        </div>
        <div class="info-row">
            <span class="info-label">User Export:</span>
            <span class="info-value">{{ Auth::user()->name ?? 'System' }}</span>
        </div>
    </div>

    {{-- Tabel Data Tiket --}}
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th width="3%" class="text-center">No</th>
                    <th width="8%">No. Tiket</th>
                    <th width="12%">Judul</th>
                    <th width="8%">Tanggal</th>
                    <th width="8%">User</th>
                    <th width="6%">Priority</th>
                    <th width="8%">SLA Due</th>
                    <th width="7%">Status SLA</th>
                    <th width="7%">Status</th>
                    <th width="8%">Kategori</th>
                    <th width="10%">Assign To</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $index => $item)
                @php
                    // Hitung status SLA
                    $slaStatus = '';
                    $slaClass = '';
                    $now = now();
                    $slaDue = isset($item->sla_due_at) ? Carbon\Carbon::parse($item->sla_due_at) : null;

                    if ($slaDue) {
                        if ($now->gt($slaDue) && !in_array($item->status, ['resolved', 'closed'])) {
                            $slaStatus = 'Overdue';
                            $slaClass = 'sla-overdue';
                        } elseif ($now->diffInHours($slaDue) <= 24 && !in_array($item->status, ['resolved', 'closed'])) {
                            $slaStatus = 'Warning (≤24h)';
                            $slaClass = 'sla-warning';
                        } elseif (in_array($item->status, ['resolved', 'closed'])) {
                            $slaStatus = 'Completed';
                            $slaClass = 'sla-ontime';
                        } else {
                            $slaStatus = 'On Time';
                            $slaClass = 'sla-ontime';
                        }
                    } else {
                        $slaStatus = 'No SLA';
                        $slaClass = 'sla-default';
                    }

                    // Priority class
                    $priorityClass = 'priority-low';
                    $priorityText = 'Low';
                    switch(strtolower($item->priority ?? 'low')) {
                        case 'critical':
                            $priorityClass = 'priority-critical';
                            $priorityText = 'Critical';
                            break;
                        case 'high':
                            $priorityClass = 'priority-high';
                            $priorityText = 'High';
                            break;
                        case 'medium':
                            $priorityClass = 'priority-medium';
                            $priorityText = 'Medium';
                            break;
                        case 'low':
                            $priorityClass = 'priority-low';
                            $priorityText = 'Low';
                            break;
                    }

                    // Status class
                    $statusClass = '';
                    $statusText = ucfirst($item->status ?? '');
                    switch(strtolower($item->status ?? '')) {
                        case 'open':
                            $statusClass = 'status-open';
                            break;
                        case 'in_progress':
                        case 'progress':
                            $statusClass = 'status-progress';
                            $statusText = 'In Progress';
                            break;
                        case 'resolved':
                            $statusClass = 'status-resolved';
                            break;
                        case 'closed':
                            $statusClass = 'status-closed';
                            break;
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center"><strong>{{ $item->ticket_number ?? '-' }}</strong></td>
                    <td>{{ strlen($item->title ?? '-') > 40 ? substr($item->title ?? '-', 0, 40) . '...' : ($item->title ?? '-') }}</td>
                    <td class="text-center">{{ isset($item->created_at) ? date('d/m/Y', strtotime($item->created_at)) : '-' }}</td>
                    <td>{{ $item->user->name ?? $item->user_id ?? '-' }}</td>
                    <td class="text-center">
                        <span class="{{ $priorityClass }}">{{ $priorityText }}</span>
                    </td>
                    <td class="text-center">
                        @if($slaDue)
                            {{ $slaDue->format('d/m/Y H:i') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="{{ $slaClass }}">{{ $slaStatus }}</span>
                    </td>
                    <td class="text-center">
                        <span class="{{ $statusClass }}">{{ $statusText }}</span>
                    </td>
                    <td class="text-center">
                        {{ $item->category->name ?? $item->category_id ?? '-' }}
                    </td>
                    <td>{{ $item->assigned_to ?? 'Unassigned' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="text-center" style="padding: 40px;">
                        <em>Tidak ada data tiket yang ditemukan</em>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Statistik Tambahan --}}
    @isset($statistics)
    <div style="margin-top: 15px; padding: 8px; background-color: #ecf0f1;">
        <strong>Ringkasan Statistik:</strong><br>
        <table style="width: 100%; margin-top: 5px; border: none;">
            <tr>
                <td style="border: none;">Total Tiket: <strong>{{ $statistics['total'] ?? 0 }}</strong></td>
                <td style="border: none;">Open: <strong>{{ $statistics['open'] ?? 0 }}</strong></td>
                <td style="border: none;">In Progress: <strong>{{ $statistics['progress'] ?? 0 }}</strong></td>
                <td style="border: none;">Resolved: <strong>{{ $statistics['resolved'] ?? 0 }}</strong></td>
                <td style="border: none;">Closed: <strong>{{ $statistics['closed'] ?? 0 }}</strong></td>
            </tr>
            <tr>
                <td style="border: none;">Critical: <strong>{{ $statistics['critical'] ?? 0 }}</strong></td>
                <td style="border: none;">High: <strong>{{ $statistics['high'] ?? 0 }}</strong></td>
                <td style="border: none;">Medium: <strong>{{ $statistics['medium'] ?? 0 }}</strong></td>
                <td style="border: none;">Low: <strong>{{ $statistics['low'] ?? 0 }}</strong></td>
                <td style="border: none;">Overdue SLA: <strong>{{ $statistics['overdue'] ?? 0 }}</strong></td>
            </tr>
        </table>
    </div>
    @endisset

    {{-- Catatan --}}
    @isset($notes)
    <div style="margin-top: 15px; padding: 8px; background-color: #fff3cd; border-left: 3px solid #ffc107; font-size: 8px;">
        <strong>Catatan:</strong><br>
        {{ $notes }}
    </div>
    @endisset

    {{-- Tanda Tangan --}}
    @if(isset($show_signature) && $show_signature)
    <div class="signature">
        <div class="signature-box">
            <div>Mengetahui,</div>
            <div style="margin-top: 5px;">Kepala Dinas</div>
            <div class="signature-line" style="margin-top: 30px;">(_________________)</div>
            <div style="font-size: 8px;">NIP. ________________</div>
        </div>
        <div class="signature-box">
            <div>Petugas,</div>
            <div style="margin-top: 5px;">Operator</div>
            <div class="signature-line" style="margin-top: 30px;">({{ Auth::user()->name ?? '_________________' }})</div>
            <div style="font-size: 8px;">NIP. ________________</div>
        </div>
    </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <div>Dokumen ini dicetak secara elektronik dan tidak memerlukan tanda tangan basah</div>
        <div>&copy; {{ date('Y') }} Sistem Manajemen Tiket - Laporan Resmi</div>
        <div>Halaman {PAGE_NUM} dari {PAGE_COUNT}</div>
    </div>
</body>
</html>
