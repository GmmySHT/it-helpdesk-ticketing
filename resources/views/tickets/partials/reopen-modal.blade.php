{{-- REOPEN MODAL --}}
<div class="modal fade" id="reopenModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('tickets.reopen', $ticket) }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">
                        <i class="fas fa-undo-alt me-2"></i>Buka Kembali Ticket #{{ $ticket->ticket_number }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Anda akan membuka kembali ticket yang sudah selesai. Ticket akan berstatus <strong>OPEN</strong>.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-comment me-1"></i>Alasan Dibuka Kembali <span class="text-danger">*</span>
                        </label>
                        <textarea name="reopen_reason"
                                  class="form-control"
                                  rows="4"
                                  required
                                  placeholder="Jelaskan mengapa ticket ini perlu dibuka kembali..."></textarea>
                        <div class="form-text">
                            Alasan ini akan dicatat dalam history ticket.
                        </div>
                    </div>

                    @if($ticket->resolution_notes)
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Solusi sebelumnya:</strong><br>
                        {{ \Illuminate\Support\Str::limit($ticket->resolution_notes, 200) }}
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-undo-alt me-1"></i> Ya, Buka Kembali
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
