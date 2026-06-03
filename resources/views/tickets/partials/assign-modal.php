<div class="modal fade" id="assignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus me-2"></i>Assign Ticket
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('tickets.assign', $ticket) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih IT Staff</label>
                        <select name="assigned_to" class="form-select" required>
                            <option value="">-- Pilih Staff --</option>
                            @foreach($itStaff as $staff)
                                <option value="{{ $staff->id }}">
                                    {{ $staff->name }} ({{ $staff->role }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Ticket akan ditugaskan ke staff yang dipilih</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Priority</label>
                        <select name="priority" class="form-select">
                            <option value="low">⚪ Rendah</option>
                            <option value="medium" selected>🟡 Sedang</option>
                            <option value="high">🔴 Tinggi</option>
                            <option value="urgent">⚫ Darurat</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">SLA Due Date (Opsional)</label>
                        <input type="datetime-local" name="sla_due_at" class="form-control">
                        <div class="form-text">Batas waktu penyelesaian ticket</div>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="notify" value="1" checked id="notifyAssignee">
                        <label class="form-check-label" for="notifyAssignee">
                            <i class="fas fa-bell me-1"></i>Kirim notifikasi ke staff yang ditugaskan
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-check me-1"></i>Assign Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>