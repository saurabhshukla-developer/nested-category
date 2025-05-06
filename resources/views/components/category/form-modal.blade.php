@php
    use App\Enums\CategoryStatus;
    use App\Models\Category;

    $parentCategories = Category::hierarchyOptions();
    $categoryStatuses = CategoryStatus::cases();
@endphp

<div class="modal fade" 
     id="categoryModal" 
     tabindex="-1" 
     aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="categoryForm" method="POST">
                @csrf
                <span id="methodSpoofContainer"></span>

                <div class="modal-header">
                    <h5 id="categoryModalTitle" class="modal-title">Add category</h5>
                    <button class="btn-close" 
                            type="button" 
                            data-bs-dismiss="modal">
                    </button>
                </div>

                <div class="modal-body">
                    <input type="hidden" 
                           name="id" 
                           id="categoryId">

                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input class="form-control"
                               type="text"
                               name="name" 
                               id="categoryName"
                               value="{{ old('name', $category->name ?? '') }}">
                        <div class="invalid-feedback" id="nameError"></div>
                    </div>
            
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select"
                                id="categoryStatus" 
                                name="status">
                            @foreach ($categoryStatuses as $statusOption)
                                <option value="{{ $statusOption->value }}"
                                        {{ old('status', $category->status ?? 1) == $statusOption->value ? 'selected' : '' }}>
                                    {{ $statusOption->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="statusError"></div>
                    </div>
            
                    <div class="mb-0">
                        <label class="form-label">Parent category</label>
                        <select class="form-select"
                                id="categoryParent" 
                                name="parent_id">
                            @foreach ($parentCategories as $parentId => $categoryPath)
                                <option value="{{ $parentId }}"
                                        {{ (string) old('parent_id', $category->parent_id ?? '') === (string) $parentId ? 'selected' : '' }}>
                                    {{ $categoryPath }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="parent_idError"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" 
                            type="button"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button class="btn btn-primary" 
                            type="submit" 
                            id="saveButton">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const elements = {
        modal: document.getElementById('categoryModal'),
        title: document.getElementById('categoryModalTitle'), 
        methodSpoof: document.getElementById('methodSpoofContainer'),
        form: document.getElementById('categoryForm'),
        inputs: {
            id: document.getElementById('categoryId'),
            name: document.getElementById('categoryName'),
            status: document.getElementById('categoryStatus'),
            parent: document.getElementById('categoryParent')
        },
        saveButton: document.getElementById('saveButton')
    };

    const resetForm = (formMode = 'add', oldData = {}) => {
        elements.methodSpoof.innerHTML = '';
        
        // Reset input values
        elements.inputs.id.value = oldData.id || '';
        elements.inputs.name.value = oldData.name || '';
        elements.inputs.status.value = oldData.status || 1;
        elements.inputs.parent.value = oldData.parentId || '';
        
        // Reset validation states
        Object.values(elements.inputs).forEach(input => {
            input.classList.remove('is-invalid');
        });

        // Clear error messages
        ['name', 'status', 'parent_id'].forEach(field => {
            document.getElementById(`${field}Error`).textContent = '';
        });

        elements.saveButton.disabled = false;
    };

    const handleModalShow = event => {
        const button = event.relatedTarget;
        const mode = button.dataset.mode;

        const formData = {
            id: button.dataset.id,
            name: button.dataset.name,
            status: button.dataset.status,
            parentId: button.dataset.parentId
        };

        resetForm(mode, formData);

        if (mode === 'edit') {
            elements.title.textContent = 'Edit category';
            elements.form.action = "{{ route('categories.update', ':id') }}".replace(':id', formData.id);
            elements.methodSpoof.innerHTML = '@method("PUT")';
        } else {
            elements.title.textContent = 'Add category';
            elements.form.action = "{{ route('categories.store') }}";
        }
    };

    const handleFormSubmit = async event => {
        event.preventDefault();
        elements.saveButton.disabled = true;
        
        try {
            const response = await fetch(elements.form.action, {
                method: elements.form.method,
                body: new FormData(elements.form),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (response.ok) {
                window.location.reload();
                return;
            }

            if (data.errors) {
                Object.entries(data.errors).forEach(([field, messages]) => {
                    const input = elements.form.querySelector(`[name="${field}"]`);
                    const error = document.getElementById(`${field}Error`);
                    
                    if (input && error) {
                        input.classList.add('is-invalid');
                        error.textContent = messages[0];
                    }
                });
            }
        } catch (error) {
            console.error('Form submission error:', error);
        } finally {
            elements.saveButton.disabled = false;
        }
    };

    const handleInputChange = event => {
        const input = event.target;
        input.classList.remove('is-invalid');
        document.getElementById(`${input.name}Error`).textContent = '';
    };

    // Event Listeners
    elements.modal.addEventListener('show.bs.modal', handleModalShow);
    elements.form.addEventListener('submit', handleFormSubmit);
    Object.values(elements.inputs).forEach(input => {
        input.addEventListener('input', handleInputChange);
    });
});
</script>
@endpush
