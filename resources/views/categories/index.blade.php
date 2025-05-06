@extends('layouts.app')

@section('content')
<x-category.delete-modal />
<x-category.form-modal />

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Categories</h1>

        <button class="btn btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#categoryModal"
                data-mode="add">
            + Add New Category
        </button>
    </div>

    {{-- flash messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- categories table --}}
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Full Path</th>
                            <th>Status</th>
                            <th>Parent ID</th>
                            <th>Created</th>
                            <th>Updated</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $cat)
                            <tr>
                                <td>{{ $cat->id }}</td>
                                <td>{{ $cat->getFullPath() }}</td>
                                <td>
                                    <span class="badge {{ $cat->getStatus()->value == 1 ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $cat->getStatusLabel() }}
                                    </span>
                                </td>
                                <td>{{ $cat->getParentId() ?? '—' }}</td>
                                <td>{{ $cat->created_at->format('Y-m-d') }}</td>
                                <td>{{ $cat->updated_at->format('Y-m-d') }}</td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-warning"
                                            data-bs-toggle="modal"
                                            data-bs-target="#categoryModal"
                                            data-mode="edit"
                                            data-id="{{ $cat->id }}"
                                            data-name="{{ $cat->getName() }}"
                                            data-status="{{ $cat->getStatus()->value }}"
                                            data-parent-id="{{ $cat->getParentId() ?? '' }}">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>

                                    <button class="btn btn-sm btn-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteModal"
                                            data-id="{{ $cat->id }}"
                                            data-name="{{ $cat->getFullPath() }}">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        No categories found
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- pagination --}}
            @if($categories->hasPages())
                <div class="d-flex justify-content-end align-items-center pt-1 mt-3">
                    <nav aria-label="Page navigation">
                        {{ $categories->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </nav>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
