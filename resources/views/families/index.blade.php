@extends('layouts.base')

@section('title', 'العائلات')

@section('content')
<div class="page-header animate-in d-flex justify-content-between align-items-center">
    <h3><i class="fas fa-users-rectangle me-2 text-primary"></i> العائلات</h3>
    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addFamilyModal">
        <i class="fas fa-plus me-1"></i> إضافة عائلة
    </button>
</div>

<div class="card card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>اسم العائلة</th>
                        <th>عدد الناخبين</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($families as $family)
                    <tr>
                        <td>{{ $loop->index + 1 }}</td>
                        <td class="fw-bold">{{ $family->name }}</td>
                        <td><span class="badge bg-primary">{{ $family->voters_count }}</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editFamilyModal{{ $family->id }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('families.destroy', $family->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('هل أنت متأكد؟')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">لا توجد عائلات</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addFamilyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة عائلة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('families.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">اسم العائلة</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary-custom">إضافة</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modals -->
@foreach($families as $family)
<div class="modal fade" id="editFamilyModal{{ $family->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تعديل عائلة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('families.update', $family->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">اسم العائلة</label>
                        <input type="text" name="name" class="form-control" value="{{ $family->name }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary-custom">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection