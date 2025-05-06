<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="deleteForm" method="POST">
                @csrf @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Delete Category</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Are you sure you want to delete <strong id="deleteName"></strong>?</p>
                    <p class="text-muted mb-0">Any child categories will be automatically reassigned to this category's parent.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('deleteModal');
    const nameEl = document.getElementById('deleteName');
    const formEl = document.getElementById('deleteForm');

    modal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        nameEl.textContent = button.dataset.name;
        formEl.action = "{{ route('categories.destroy', ':id') }}".replace(':id', button.dataset.id);
    });
});
</script>
@endpush