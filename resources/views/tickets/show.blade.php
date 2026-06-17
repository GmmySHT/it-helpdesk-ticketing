@extends('layouts.app')

@section('title', 'Ticket #' . $ticket->ticket_number)

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/ticket-show.css') }}">
@endpush

@php
    /* Pre-compute values once ─ avoid repeating logic in the view */
    $isOverdue = $ticket->sla_due_at
        && \Carbon\Carbon::now()->gt(\Carbon\Carbon::parse($ticket->sla_due_at))
        && !in_array($ticket->status, ['resolved','closed']);

    $slaUsedPct = 0;
    if ($ticket->sla_due_at && $ticket->created_at) {
        $total   = $ticket->created_at->diffInMinutes(\Carbon\Carbon::parse($ticket->sla_due_at));
        $elapsed = $ticket->created_at->diffInMinutes(now());
        $slaUsedPct = $total > 0 ? min(round($elapsed / $total * 100), 100) : 0;
    }

    $priorityDotMap = [
        'low'    => 'fa-arrow-down',
        'medium' => 'fa-minus',
        'high'   => 'fa-arrow-up',
        'urgent' => 'fa-exclamation-triangle',
    ];
    $statusIconMap = [
        'open'        => 'fa-envelope',
        'in_queue'    => 'fa-layer-group',
        'in_progress' => 'fa-cogs',
        'resolved'    => 'fa-check-circle',
        'closed'      => 'fa-archive',
    ];
    $actionDotMap = [
        'created'        => ['dot' => 'blue',   'icon' => 'fas fa-plus',         'tag' => 'created'],
        'updated'        => ['dot' => 'gray',    'icon' => 'fas fa-edit',         'tag' => 'updated'],
        'assigned'       => ['dot' => 'amber',   'icon' => 'fas fa-user-plus',    'tag' => 'assigned'],
        'taken'          => ['dot' => 'green',   'icon' => 'fas fa-hand-paper',   'tag' => 'taken'],
        'status_changed' => ['dot' => 'purple',  'icon' => 'fas fa-sync-alt',     'tag' => 'status_changed'],
        'resolved'       => ['dot' => 'green',   'icon' => 'fas fa-check-circle', 'tag' => 'resolved'],
        'reopened'       => ['dot' => 'red',     'icon' => 'fas fa-undo-alt',     'tag' => 'reopened'],
    ];

    $backRoute = auth()->user()->role === 'admin'
        ? route('tickets.index')
        : (request()->routeIs('it.tickets.show') ? route('it.tickets.my') : route('tickets.index'));

    $statusRoute = auth()->user()->role === 'admin'
        ? route('tickets.status', $ticket)
        : route('it.tickets.status', $ticket);

    $reopenRoute = auth()->user()->role === 'admin'
        ? route('tickets.reopen', $ticket)
        : route('it.tickets.reopen', $ticket);

    $histories = $ticket->histories()->with('user')->latest()->get();

    $attachments = null;
    if ($ticket->resolution_attachments) {
        $attachments = is_string($ticket->resolution_attachments)
            ? json_decode($ticket->resolution_attachments, true)
            : $ticket->resolution_attachments;
    }
@endphp

@section('content')
<div class="ts-page container-fluid px-4">

    {{-- ═══════════════ HEADER ═══════════════ --}}
    <header class="ts-header">
        <div class="ts-header-inner">
            <div>
                <h1 class="ts-header-title">
                    <i class="fas fa-ticket-alt" aria-hidden="true"></i>
                    Ticket #{{ $ticket->ticket_number }}
                </h1>
                <div class="ts-header-badges">
                    <span class="ts-badge ts-badge-white">
                        <i class="fas {{ $priorityDotMap[$ticket->priority] ?? 'fa-minus' }}" aria-hidden="true"></i>
                        {{ ucfirst($ticket->priority) }}
                    </span>
                    <span class="ts-badge ts-badge-white">
                        <i class="fas {{ $statusIconMap[$ticket->status] ?? 'fa-circle' }}" aria-hidden="true"></i>
                        {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                    </span>
                    @if($ticket->reopen_count > 0)
                    <span class="ts-badge ts-badge-white" title="Dibuka kembali {{ $ticket->reopen_count }} kali">
                        <i class="fas fa-undo-alt" aria-hidden="true"></i>
                        Reopen ×{{ $ticket->reopen_count }}
                    </span>
                    @endif
                </div>
                <p class="ts-header-sub">{{ strip_tags($ticket->title) }}</p>
            </div>
            <a href="{{ $backRoute }}" class="ts-btn-back">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>Kembali ke Daftar
            </a>
        </div>
    </header>

    {{-- ═══════════════ MAIN GRID ═══════════════ --}}
    <div class="ts-grid">

        {{-- ─── LEFT COLUMN ─── --}}
        <div>

            {{-- Description --}}
            <div class="ts-card">
                <div class="ts-card-head">
                    <div class="ts-card-head-left">
                        <div class="ts-card-icon blue" aria-hidden="true"><i class="fas fa-file-alt"></i></div>
                        <div>
                            <h2 class="ts-card-title">Deskripsi ticket</h2>
                            <div class="ts-card-sub">
                                <i class="fas fa-user" aria-hidden="true"></i>
                                {{ $ticket->user->name }}
                                &nbsp;·&nbsp;
                                <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                                {{ $ticket->created_at->translatedFormat('d M Y, H:i') }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ts-card-body">
                    <div class="ts-desc-box">{!! $ticket->description !!}</div>
                </div>
            </div>

            {{-- Resolution (only when resolved + has notes) --}}
            @if($ticket->status === 'resolved' && $ticket->resolution_notes)
            <div class="ts-card ts-card-resolve">
                <div class="ts-resolve-head">
                    <div class="ts-resolve-icon" aria-hidden="true"><i class="fas fa-check"></i></div>
                    <div>
                        <h2 class="ts-resolve-title">Solusi penyelesaian</h2>
                        <div class="ts-resolve-sub">
                            <i class="fas fa-user-check" aria-hidden="true"></i>
                            Diselesaikan oleh: {{ $ticket->resolvedBy->name ?? $ticket->assignedTo->name ?? 'System' }}
                            &nbsp;·&nbsp;
                            <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                            {{ $ticket->resolved_at ? \Carbon\Carbon::parse($ticket->resolved_at)->translatedFormat('d M Y, H:i') : '-' }}
                        </div>
                    </div>
                </div>
                <div class="ts-card-body">
                    <div class="ts-resolve-box">{!! $ticket->resolution_notes !!}</div>

                    {{-- 🔥 TAMPILAN LAMPIRAN YANG DIPERBAIKI --}}
                    @php
                        $attachments = null;
                        if ($ticket->resolution_attachments) {
                            $attachments = is_string($ticket->resolution_attachments)
                                ? json_decode($ticket->resolution_attachments, true)
                                : $ticket->resolution_attachments;
                        }
                    @endphp

                    @if(!empty($attachments) && is_array($attachments) && count($attachments) > 0)
                        <hr style="border:none;border-top:1px solid #bbf7d0;margin:1.25rem 0">

                        <div class="ts-attach-title">
                            <i class="fas fa-paperclip" aria-hidden="true"></i>
                            Lampiran bukti
                            <span class="ts-attach-badge">{{ count($attachments) }} file</span>
                        </div>

                        <div class="ts-attach-grid">
                            @foreach($attachments as $index => $att)
                                @php
                                    $path = $att['path'] ?? $att['file_path'] ?? null;
                                    $name = $att['name'] ?? $att['original_name'] ?? 'File ' . ($index + 1);
                                    $size = $att['size'] ?? $att['file_size'] ?? 0;
                                    $mime = $att['mime'] ?? $att['mime_type'] ?? 'application/octet-stream';
                                    $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

                                    // Cek apakah file ada di storage
                                    $fileExists = $path && Storage::disk('public')->exists($path);

                                    // Tentukan icon berdasarkan mime/extension
                                    $icon = 'fa-file';
                                    $iconColor = '#6b7280';
                                    $isImage = false;

                                    if (str_contains($mime, 'image') || in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'])) {
                                        $icon = 'fa-image';
                                        $iconColor = '#1d6fb8';
                                        $isImage = true;
                                    } elseif (str_contains($mime, 'pdf') || $extension === 'pdf') {
                                        $icon = 'fa-file-pdf';
                                        $iconColor = '#e11d48';
                                    } elseif (str_contains($mime, 'word') || str_contains($mime, 'document') || in_array($extension, ['doc', 'docx'])) {
                                        $icon = 'fa-file-word';
                                        $iconColor = '#2563eb';
                                    } elseif (str_contains($mime, 'excel') || str_contains($mime, 'spreadsheet') || in_array($extension, ['xls', 'xlsx'])) {
                                        $icon = 'fa-file-excel';
                                        $iconColor = '#059669';
                                    } elseif (str_contains($mime, 'zip') || str_contains($mime, 'rar') || in_array($extension, ['zip', 'rar', '7z'])) {
                                        $icon = 'fa-file-archive';
                                        $iconColor = '#d97706';
                                    } elseif (str_contains($mime, 'text') || in_array($extension, ['txt', 'log'])) {
                                        $icon = 'fa-file-alt';
                                        $iconColor = '#6b7280';
                                    } elseif (str_contains($mime, 'video') || in_array($extension, ['mp4', 'avi', 'mkv', 'mov'])) {
                                        $icon = 'fa-file-video';
                                        $iconColor = '#7c3aed';
                                    } elseif (str_contains($mime, 'audio') || in_array($extension, ['mp3', 'wav', 'flac'])) {
                                        $icon = 'fa-file-audio';
                                        $iconColor = '#8b5cf6';
                                    }
                                @endphp

                                @if($path && $fileExists)
                                    <a href="{{ Storage::url($path) }}"
                                    target="_blank"
                                    class="ts-attach-card"
                                    title="{{ $name }} ({{ $size > 0 ? round($size / 1024, 1) . ' KB' : '0 KB' }})">

                                        {{-- Jika gambar, tampilkan thumbnail --}}
                                        @if($isImage)
                                            <img src="{{ Storage::url($path) }}"
                                                alt="{{ $name }}"
                                                class="ts-attach-preview"
                                                loading="lazy">
                                        @else
                                            <i class="fas {{ $icon }}" style="color:{{ $iconColor }}" aria-hidden="true"></i>
                                        @endif

                                        <div class="ts-attach-name" title="{{ $name }}">
                                            {{ \Illuminate\Support\Str::limit($name, 25) }}
                                        </div>

                                        <div class="ts-attach-size">
                                            {{ $size > 0 ? round($size / 1024, 1) . ' KB' : '-' }}
                                        </div>

                                        <div class="ts-attach-download">
                                            <i class="fas fa-download" aria-hidden="true"></i> Download
                                        </div>
                                    </a>
                                @else
                                    <div class="ts-attach-card ts-attach-error">
                                        <i class="fas fa-exclamation-triangle" style="color:#dc2626;font-size:28px;" aria-hidden="true"></i>
                                        <div class="ts-attach-name" style="color:#dc2626;">File tidak ditemukan</div>
                                        <div class="ts-attach-size">{{ $name }}</div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            @endif

        </div>{{-- /left column --}}

        {{-- ─── RIGHT COLUMN (SIDEBAR) ─── --}}
        <div>

            {{-- Info Card --}}
            <div class="ts-card">
                <div class="ts-card-head">
                    <div class="ts-card-head-left">
                        <h2 class="ts-card-title" style="display:flex;align-items:center;gap:7px">
                            <i class="fas fa-info-circle" style="color:#1d6fb8" aria-hidden="true"></i>
                            Informasi ticket
                        </h2>
                    </div>
                </div>
                <div class="ts-card-body">
                    <div class="ts-info-list">

                        <div class="ts-info-row">
                            <div class="ts-info-lbl"><i class="fas fa-tag" aria-hidden="true"></i>Kategori</div>
                            <div class="ts-info-val">
                                <span class="ts-badge" style="background:{{ $ticket->category->color ?? '#ede9fe' }};color:{{ $ticket->category->color ? '#fff' : '#4338ca' }}">
                                    {{ $ticket->category->name ?? '-' }}
                                </span>
                            </div>
                        </div>

                        <div class="ts-info-row">
                            <div class="ts-info-lbl"><i class="fas fa-barcode" aria-hidden="true"></i>Nomor ticket</div>
                            <div class="ts-info-val bold">{{ $ticket->ticket_number }}</div>
                        </div>

                        <div class="ts-info-row">
                            <div class="ts-info-lbl"><i class="fas fa-calendar-plus" aria-hidden="true"></i>Tanggal dibuat</div>
                            <div class="ts-info-val">{{ $ticket->created_at->translatedFormat('d M Y, H:i') }}</div>
                        </div>

                        <div class="ts-info-row">
                            <div class="ts-info-lbl"><i class="fas fa-calendar-check" aria-hidden="true"></i>Terakhir update</div>
                            <div class="ts-info-val">{{ $ticket->updated_at->diffForHumans() }}</div>
                        </div>

                        <div class="ts-info-row">
                            <div class="ts-info-lbl"><i class="fas fa-user-check" aria-hidden="true"></i>Ditugaskan ke</div>
                            <div class="ts-info-val">
                                @if($ticket->assignedTo)
                                    <div class="ts-avatar-row">
                                        <div class="ts-avatar" aria-hidden="true">
                                            {{ strtoupper(substr($ticket->assignedTo->name, 0, 2)) }}
                                        </div>
                                        <span>{{ $ticket->assignedTo->name }}</span>
                                    </div>
                                @else
                                    <span class="ts-info-val muted">
                                        <i class="fas fa-user-slash" aria-hidden="true"></i> Belum ditugaskan
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if($ticket->assigned_at)
                        <div class="ts-info-row">
                            <div class="ts-info-lbl"><i class="fas fa-clock" aria-hidden="true"></i>Ditugaskan pada</div>
                            <div class="ts-info-val">{{ \Carbon\Carbon::parse($ticket->assigned_at)->translatedFormat('d M Y, H:i') }}</div>
                        </div>
                        @endif

                        @if($ticket->resolved_at)
                        <div class="ts-info-row">
                            <div class="ts-info-lbl"><i class="fas fa-check-circle" aria-hidden="true"></i>Diselesaikan</div>
                            <div class="ts-info-val success">{{ \Carbon\Carbon::parse($ticket->resolved_at)->translatedFormat('d M Y, H:i') }}</div>
                        </div>
                        @endif

                        @if($ticket->sla_due_at)
                        <div class="ts-info-row">
                            <div class="ts-info-lbl"><i class="fas fa-hourglass-half" aria-hidden="true"></i>SLA deadline</div>
                            <div class="ts-info-val {{ $isOverdue ? 'danger' : '' }}">
                                {{ \Carbon\Carbon::parse($ticket->sla_due_at)->translatedFormat('d M Y, H:i') }}
                                @if($isOverdue)
                                    <span class="ts-badge ts-badge-priority-urgent" style="margin-top:3px;display:inline-flex">
                                        <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>Overdue
                                    </span>
                                @endif
                                <div class="ts-sla-bar" role="progressbar" aria-valuenow="{{ $slaUsedPct }}" aria-valuemin="0" aria-valuemax="100">
                                    <div class="ts-sla-fill" style="width:{{ $slaUsedPct }}%;background:{{ $isOverdue ? '#e11d48' : '#d97706' }}"></div>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($ticket->reopen_count > 0)
                        <div class="ts-info-row">
                            <div class="ts-info-lbl"><i class="fas fa-undo-alt" aria-hidden="true"></i>Jumlah reopen</div>
                            <div class="ts-info-val">
                                <span class="ts-badge ts-badge-reopen">{{ $ticket->reopen_count }} kali</span>
                                @if($ticket->reopened_at)
                                <div style="font-size:.68rem;color:#9ca3af;margin-top:3px">
                                    Terakhir: {{ \Carbon\Carbon::parse($ticket->reopened_at)->translatedFormat('d M Y, H:i') }}
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="ts-card">
                <div class="ts-card-head">
                    <h2 class="ts-card-title" style="display:flex;align-items:center;gap:7px">
                        <i class="fas fa-bolt" style="color:#d97706" aria-hidden="true"></i>Aksi cepat
                    </h2>
                </div>
                <div class="ts-card-body">
                    <div class="ts-actions">

                        {{-- ✅ TAKE TICKET (untuk IT Staff & Admin) --}}
                        @if(auth()->user()->role === 'admin' || auth()->user()->role === 'it_staff' || auth()->user()->role === 'it')
                            @if(is_null($ticket->assigned_to) && in_array($ticket->status, ['open', 'in_queue']))
                                @php
                                    $takeRoute = auth()->user()->role === 'admin'
                                        ? route('tickets.take', $ticket)
                                        : route('it.tickets.take', $ticket);
                                @endphp
                                <form action="{{ $takeRoute }}" method="POST" style="width:100%">
                                    @csrf
                                    <button type="submit" class="ts-act-btn ts-act-blue" style="width:100%">
                                        <i class="fas fa-hand-paper" aria-hidden="true"></i>Ambil ticket ini
                                    </button>
                                </form>
                            @endif
                        @endif

                        {{-- ✅ RESOLVE TICKET (untuk IT Staff & Admin) --}}
                        @if(auth()->user()->role === 'admin' || auth()->user()->role === 'it_staff' || auth()->user()->role === 'it')
                            @if(!in_array($ticket->status, ['resolved','closed']))
                                <button type="button" class="ts-act-btn ts-act-green"
                                        data-bs-toggle="modal" data-bs-target="#resolveModal">
                                    <i class="fas fa-check-circle" aria-hidden="true"></i>Selesaikan ticket
                                </button>
                            @endif
                        @endif

                        {{-- ✅ REOPEN TICKET (untuk IT Staff & Admin) --}}
                        @if(auth()->user()->role === 'admin' || auth()->user()->role === 'it_staff' || auth()->user()->role === 'it')
                            @if(in_array($ticket->status, ['resolved','closed']))
                                <button type="button" class="ts-act-btn ts-act-amber"
                                        data-bs-toggle="modal" data-bs-target="#reopenModal">
                                    <i class="fas fa-undo-alt" aria-hidden="true"></i>Buka kembali ticket
                                </button>
                            @endif
                        @endif

                        {{-- ✅ UPDATE STATUS (untuk Admin & IT Staff) --}}
                        @if(auth()->user()->role === 'admin' || auth()->user()->role === 'it_staff' || auth()->user()->role === 'it')
                            @if(!in_array($ticket->status, ['resolved','closed']))
                            <div class="dropdown" style="width:100%">
                                <button class="ts-act-btn ts-act-amber dropdown-toggle" style="width:100%"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-exchange-alt" aria-hidden="true"></i>Ubah Status
                                </button>
                                <ul class="dropdown-menu" style="width:100%">
                                    @php
                                        $statusOptions = [];
                                        if (auth()->user()->role === 'admin') {
                                            $statusOptions = ['open', 'in_queue', 'in_progress', 'resolved', 'closed'];
                                        } else {
                                            $statusOptions = ['in_queue', 'in_progress', 'resolved'];
                                        }
                                    @endphp
                                    @foreach($statusOptions as $status)
                                        @if($ticket->status != $status)
                                            <li>
                                                <form action="{{ $statusRoute }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="status" value="{{ $status }}">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="fas {{ $statusIconMap[$status] ?? 'fa-circle' }} me-2" aria-hidden="true"></i>
                                                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                        @endif

                        {{-- ✅ EDIT TICKET (hanya Admin) --}}
                        @can('update', $ticket)
                            @if(auth()->user()->role === 'admin')
                                <a href="{{ route('tickets.edit', $ticket) }}" class="ts-act-btn ts-act-amber" style="width:100%;text-align:center">
                                    <i class="fas fa-edit" aria-hidden="true"></i>Edit ticket
                                </a>
                            @endif
                        @endcan

                        {{-- ✅ DELETE TICKET (hanya Admin) --}}
                        @can('delete', $ticket)
                            @if(auth()->user()->role === 'admin')
                                <button type="button" class="ts-act-btn ts-act-red"
                                        data-bs-toggle="modal" data-bs-target="#deleteModal">
                                    <i class="fas fa-trash" aria-hidden="true"></i>Hapus ticket
                                </button>
                            @endif
                        @endcan

                    </div>
                </div>
            </div>

            {{-- Activity Timeline --}}
            <div class="ts-card">
                <div class="ts-card-head">
                    <div>
                        <h2 class="ts-card-title" style="display:flex;align-items:center;gap:7px">
                            <i class="fas fa-history" style="color:#0d9488" aria-hidden="true"></i>Riwayat aktivitas
                        </h2>
                        <div class="ts-card-sub">Semua perubahan pada ticket ini</div>
                    </div>
                    <span style="font-size:.72rem;color:#9ca3af">{{ $histories->count() }} aktivitas</span>
                </div>

                <div class="ts-card-body ts-timeline-scroll">
                    <div class="ts-timeline">
                        @forelse($histories as $history)
                        @php
                            $mapEntry = $actionDotMap[$history->action] ?? ['dot' => 'gray', 'icon' => 'fas fa-circle', 'tag' => $history->action];
                            $meta     = $history->meta ? json_decode($history->meta, true) : null;
                        @endphp
                        <div class="ts-tl-item">
                            <div class="ts-tl-dot {{ $mapEntry['dot'] }}" aria-hidden="true">
                                <i class="{{ $mapEntry['icon'] }}"></i>
                            </div>
                            <div class="ts-tl-card">
                                <div class="ts-tl-head">
                                    <div>
                                        <span class="ts-tl-actor">{{ $history->user->name ?? 'System' }}</span>
                                        <span class="ts-tl-time"> · {{ $history->created_at->diffForHumans() }}</span>
                                    </div>
                                    <span class="ts-tl-tag {{ $mapEntry['tag'] }}">
                                        {{ ucfirst(str_replace('_', ' ', $history->action)) }}
                                    </span>
                                </div>
                                <div class="ts-tl-note">{{ $history->notes }}</div>
                                @if($history->action === 'reopened' && isset($meta['reason']))
                                <div class="ts-tl-reopen-reason">
                                    <i class="fas fa-comment" aria-hidden="true"></i>
                                    <span><strong>Alasan:</strong> {{ $meta['reason'] }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div style="text-align:center;padding:2rem 0;color:#9ca3af">
                            <i class="fas fa-history" style="font-size:2rem;opacity:.4;display:block;margin-bottom:.5rem" aria-hidden="true"></i>
                            <p style="font-size:.85rem;margin:0">Belum ada riwayat aktivitas</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                @if($histories->count() > 0)
                <div class="ts-tl-footer">
                    <div class="ts-tl-footer-text">
                        <i class="fas fa-clock" aria-hidden="true"></i>
                        Menampilkan {{ $histories->count() }} aktivitas
                    </div>
                </div>
                @endif
            </div>

        </div>{{-- /sidebar --}}
    </div>{{-- /ts-grid --}}

</div>{{-- /ts-page --}}

{{-- ═══════════════ MODAL: RESOLVE ═══════════════ --}}
<div class="modal fade" id="resolveModal" tabindex="-1" aria-labelledby="resolveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header resolve">
                <h5 class="modal-title text-white" id="resolveModalLabel">
                    <i class="fas fa-check-circle me-2" aria-hidden="true"></i>
                    Selesaikan Ticket #{{ $ticket->ticket_number }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form action="{{ $statusRoute }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="status" value="resolved">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2" aria-hidden="true"></i>
                        <strong>Ticket:</strong> {{ $ticket->ticket_number }} — {{ strip_tags($ticket->title) }}
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="resolution_notes">
                            <i class="fas fa-comment-dots me-1 text-danger" aria-hidden="true"></i>
                            Deskripsi penyelesaian <span class="text-danger">*</span>
                        </label>
                        <textarea id="resolution_notes" name="resolution_notes" class="form-control" rows="5" required
                                  placeholder="Jelaskan langkah-langkah penyelesaian, solusi yang diterapkan, dan hasil akhir…"></textarea>
                        <div class="form-text">
                            <i class="fas fa-lightbulb me-1 text-warning" aria-hidden="true"></i>
                            Berikan penjelasan detail agar user memahami solusi yang diberikan.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="resolution_attachments">
                            <i class="fas fa-paperclip me-1" aria-hidden="true"></i>Lampiran bukti (opsional)
                        </label>
                        <input id="resolution_attachments" type="file" name="resolution_attachments[]"
                               class="form-control" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt">
                        <div class="form-text">
                            Gambar, PDF, atau dokumen sebagai bukti penyelesaian (maks 5 MB per file).
                        </div>
                    </div>
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-2" aria-hidden="true"></i>
                        <strong>Perhatian:</strong> Setelah diselesaikan, status tidak dapat diubah kecuali dibuka kembali.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1" aria-hidden="true"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-circle me-1" aria-hidden="true"></i>Konfirmasi selesai
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════════ MODAL: REOPEN ═══════════════ --}}
<div class="modal fade" id="reopenModal" tabindex="-1" aria-labelledby="reopenModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ $reopenRoute }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header reopen">
                    <h5 class="modal-title" id="reopenModalLabel">
                        <i class="fas fa-undo-alt me-2" aria-hidden="true"></i>
                        Buka Kembali Ticket #{{ $ticket->ticket_number }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2" aria-hidden="true"></i>
                        Ticket akan kembali berstatus <strong>OPEN</strong> dan dapat dikerjakan ulang.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="reopen_reason">
                            <i class="fas fa-comment me-1" aria-hidden="true"></i>
                            Alasan dibuka kembali <span class="text-danger">*</span>
                        </label>
                        <textarea id="reopen_reason" name="reopen_reason" class="form-control" rows="4" required
                                  placeholder="Jelaskan mengapa ticket ini perlu dibuka kembali…"></textarea>
                        <div class="form-text">Alasan akan dicatat dalam riwayat ticket.</div>
                    </div>
                    @if($ticket->resolution_notes)
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2" aria-hidden="true"></i>
                        <strong>Solusi sebelumnya:</strong><br>
                        {{ \Illuminate\Support\Str::limit(strip_tags($ticket->resolution_notes), 200) }}
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1" aria-hidden="true"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-undo-alt me-1" aria-hidden="true"></i>Ya, buka kembali
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════════ MODAL: DELETE ═══════════════ --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header delete">
                <h5 class="modal-title text-white" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle me-2" aria-hidden="true"></i>Konfirmasi hapus
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus ticket <strong>#{{ $ticket->ticket_number }}</strong>?</p>
                <p class="text-danger mb-0">
                    <i class="fas fa-info-circle me-1" aria-hidden="true"></i>
                    Tindakan ini tidak dapat dibatalkan dan semua data terkait akan dihapus permanen.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1" aria-hidden="true"></i>Batal
                </button>
                <form action="{{ route('tickets.destroy', $ticket) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1" aria-hidden="true"></i>Ya, hapus permanen
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Buat overlay sekali
    const overlay = document.createElement('div');
    overlay.className = 'ts-img-overlay';
    document.body.appendChild(overlay);

    let activeImg = null;

    // Semua gambar di dalam deskripsi & resolusi
    document.querySelectorAll('.ts-desc-box img, .ts-resolve-box img').forEach(function (img) {
        img.addEventListener('click', function () {
            activeImg === img ? closeZoom() : openZoom(img);
        });
    });

    overlay.addEventListener('click', closeZoom);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeZoom();
    });

    function openZoom(img) {
        if (activeImg) activeImg.classList.remove('ts-img-zoomed');
        activeImg = img;
        img.classList.add('ts-img-zoomed');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeZoom() {
        if (activeImg) { activeImg.classList.remove('ts-img-zoomed'); activeImg = null; }
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }
});
</script>
@endpush

