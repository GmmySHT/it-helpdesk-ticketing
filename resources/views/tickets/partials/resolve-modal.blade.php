{{-- Modal Resolve - Partial untuk digunakan di berbagai halaman --}}
<div class="modal fade" id="resolveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i>Resolve Ticket
                    <span id="modalTicketNumber"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="resolveForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="status" value="resolved">
                <input type="hidden" id="ticketId" name="ticket_id">

                <div class="modal-body">
                    <div class="mb-4">
                        <h6 class="fw-bold mb-2" id="modalTicketTitle"></h6>
                        <small class="text-muted" id="modalTicketCategory"></small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-comment-dots me-1"></i>Deskripsi Penyelesaian <span class="text-danger">*</span>
                        </label>
                        <div class="alert alert-warning py-2 mb-2">
                            <small>
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Wajib diisi untuk menjelaskan bagaimana issue diselesaikan
                            </small>
                        </div>
                        <textarea name="resolution_notes" id="resolution_notes" class="form-control" rows="5" required placeholder="Jelaskan langkah-langkah penyelesaian, solusi yang diterapkan, dan hasil akhir..."></textarea>
                        <div class="form-text mt-2">
                            <i class="fas fa-lightbulb me-1"></i>
                            Jelaskan langkah-langkah penyelesaian, solusi yang diterapkan, dan hasil akhir
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-paperclip me-1"></i>Lampiran Bukti (Opsional)
                        </label>
                        <div class="input-group">
                            <input type="file" name="resolution_attachments[]" id="resolutionAttachments"
                                   class="form-control"
                                   multiple
                                   accept="image/*,.pdf,.doc,.docx,.xls,.xlsx">
                            <button class="btn btn-outline-secondary" type="button" id="clearFilesBtn">
                                <i class="fas fa-times"></i> Hapus
                            </button>
                        </div>
                        <div class="form-text">
                            Upload gambar, PDF, atau dokumen sebagai bukti penyelesaian (maks 5MB per file)
                        </div>
                        <div id="filePreview" class="mt-2"></div>
                    </div>

                    <div class="alert alert-info">
                        <div class="d-flex">
                            <i class="fas fa-info-circle me-2 mt-1"></i>
                            <div>
                                <small>
                                    Setelah di-resolve, ticket akan masuk ke status "Resolved".
                                    User dapat memberikan feedback sebelum ticket ditutup.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-success" id="submitResolveBtn">
                        <i class="fas fa-check-circle me-1"></i>Konfirmasi Resolve
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Fungsi untuk menampilkan modal resolve
function showResolveModal(ticketId, ticketNumber, ticketTitle) {
    // Update modal content
    document.getElementById('modalTicketNumber').textContent = '#' + ticketNumber;
    document.getElementById('modalTicketTitle').textContent = ticketTitle;
    document.getElementById('ticketId').value = ticketId;

    // Reset form
    const form = document.getElementById('resolveForm');
    if (form) form.reset();

    const resolutionNotes = document.getElementById('resolution_notes');
    if (resolutionNotes) resolutionNotes.value = '';

    const filePreview = document.getElementById('filePreview');
    if (filePreview) filePreview.innerHTML = '';

    // Update form action
    const resolveForm = document.getElementById('resolveForm');
    if (resolveForm) {
        resolveForm.action = '/tickets/' + ticketId + '/status';
    }

    // Reset button
    const submitBtn = document.getElementById('submitResolveBtn');
    if (submitBtn) {
        submitBtn.innerHTML = '<i class="fas fa-check-circle me-1"></i>Konfirmasi Resolve';
        submitBtn.disabled = false;
    }

    // Show modal
    const modalElement = document.getElementById('resolveModal');
    if (modalElement) {
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    }
}

// Hapus backdrop yang stuck
function removeStuckBackdrop() {
    const backdrops = document.querySelectorAll('.modal-backdrop');
    backdrops.forEach(backdrop => backdrop.remove());
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
}

document.addEventListener('DOMContentLoaded', function() {
    removeStuckBackdrop();

    // Event ketika modal ditutup
    const resolveModal = document.getElementById('resolveModal');
    if (resolveModal) {
        resolveModal.addEventListener('hidden.bs.modal', function() {
            removeStuckBackdrop();
        });
    }

    // File preview handler
    const fileInput = document.getElementById('resolutionAttachments');
    const filePreview = document.getElementById('filePreview');
    const clearFilesBtn = document.getElementById('clearFilesBtn');

    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (filePreview) filePreview.innerHTML = '';

            if (this.files.length > 0) {
                let html = '<div class="list-group mt-2">';
                for (let i = 0; i < this.files.length; i++) {
                    const file = this.files[i];
                    if (file.size > 5 * 1024 * 1024) {
                        Swal.fire({
                            icon: 'error',
                            title: 'File Terlalu Besar',
                            text: `File ${file.name} melebihi batas 5MB`,
                            confirmButtonText: 'Mengerti'
                        });
                        this.value = '';
                        if (filePreview) filePreview.innerHTML = '';
                        return;
                    }
                    const icon = file.type.startsWith('image/') ? 'fa-image' :
                                file.type === 'application/pdf' ? 'fa-file-pdf' : 'fa-file';
                    const color = file.type.startsWith('image/') ? 'primary' :
                                 file.type === 'application/pdf' ? 'danger' : 'secondary';
                    html += `
                        <div class="list-group-item">
                            <div class="d-flex align-items-center">
                                <i class="fas ${icon} me-2 text-${color}"></i>
                                <div class="flex-grow-1">
                                    <small class="d-block">${escapeHtml(file.name)}</small>
                                    <small class="text-muted">${(file.size / 1024).toFixed(2)} KB</small>
                                </div>
                            </div>
                        </div>
                    `;
                }
                html += '</div>';
                if (filePreview) filePreview.innerHTML = html;
            }
        });
    }

    if (clearFilesBtn) {
        clearFilesBtn.addEventListener('click', function() {
            if (fileInput) {
                fileInput.value = '';
                if (filePreview) filePreview.innerHTML = '';
            }
        });
    }

    // Escape HTML function
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Form validation
    const resolveForm = document.getElementById('resolveForm');
    if (resolveForm) {
        resolveForm.addEventListener('submit', function(e) {
            const resolutionNotes = document.getElementById('resolution_notes');
            const content = resolutionNotes ? resolutionNotes.value.trim() : '';
            const submitBtn = document.getElementById('submitResolveBtn');

            if (!content) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Deskripsi Penyelesaian Wajib Diisi',
                    text: 'Silakan isi deskripsi penyelesaian sebelum melanjutkan.',
                    confirmButtonText: 'Mengerti'
                });
                return false;
            }

            if (content.length < 10) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Deskripsi Terlalu Pendek',
                    text: 'Silakan berikan penjelasan yang lebih detail (minimal 10 karakter).',
                    confirmButtonText: 'Mengerti'
                });
                return false;
            }

            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Memproses...';
                submitBtn.disabled = true;
            }
        });
    }
});
</script>
@endpush
