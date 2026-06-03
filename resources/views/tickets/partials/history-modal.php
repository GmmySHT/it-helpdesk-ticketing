<div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-history me-2"></i>History Ticket #{{ $ticket->ticket_number }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="timeline">
                    @foreach($ticket->histories()->with('user')->latest()->get() as $history)
                    <div class="timeline-item mb-4">
                        <div class="timeline-marker bg-{{ 
                            $history->action === 'created' ? 'primary' : 
                            ($history->action === 'updated' ? 'info' : 
                            ($history->action === 'assigned' ? 'warning' : 
                            ($history->action === 'taken' ? 'success' : 
                            ($history->action === 'status_changed' ? 'secondary' : 'dark')))) 
                        }}">
                            @switch($history->action)
                                @case('created') <i class="fas fa-plus"></i> @break
                                @case('updated') <i class="fas fa-edit"></i> @break
                                @case('assigned') <i class="fas fa-user-plus"></i> @break
                                @case('taken') <i class="fas fa-hand-paper"></i> @break
                                @case('status_changed') <i class="fas fa-sync-alt"></i> @break
                                @default <i class="fas fa-circle"></i>
                            @endswitch
                        </div>
                        <div class="timeline-content">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title mb-0">
                                            {{ ucfirst(str_replace('_', ' ', $history->action)) }}
                                        </h6>
                                        <small class="text-muted">
                                            {{ $history->created_at->format('d/m/Y H:i') }}
                                        </small>
                                    </div>
                                    <p class="card-text small mb-1">{{ $history->notes }}</p>
                                    <small class="text-muted">
                                        <i class="fas fa-user me-1"></i>
                                        {{ $history->user->name ?? 'System' }}
                                    </small>
                                    
                                    @if($history->meta)
                                        @php
                                            $meta = json_decode($history->meta, true);
                                            if(is_array($meta) && !empty($meta)) {
                                                echo '<div class="mt-2">';
                                                foreach($meta as $key => $value) {
                                                    if(is_array($value) && isset($value['old'], $value['new'])) {
                                                        echo '<small class="badge bg-light text-dark me-1 mb-1">';
                                                        echo str_replace('_', ' ', $key) . ': ' . 
                                                             ($value['old'] ?: '<em>empty</em>') . 
                                                             ' → ' . ($value['new'] ?: '<em>empty</em>');
                                                        echo '</small>';
                                                    }
                                                }
                                                echo '</div>';
                                            }
                                        @endphp
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    
                    @if($ticket->histories->count() === 0)
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-history fa-3x mb-3"></i>
                        <p>Belum ada history untuk ticket ini</p>
                    </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .timeline {
        position: relative;
        padding-left: 40px;
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 20px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }
    .timeline-item {
        position: relative;
    }
    .timeline-marker {
        position: absolute;
        left: -40px;
        top: 20px;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        z-index: 1;
    }
    .timeline-content {
        margin-left: 20px;
    }
</style>