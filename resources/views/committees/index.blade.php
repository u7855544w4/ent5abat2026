@extends('layouts.base')

@section('title', 'اللجان')

@section('content')
<div class="page-header animate-in d-flex justify-content-between align-items-center">
    <h3><i class="fas fa-clipboard-list me-2 text-primary"></i> اللجان</h3>
    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addCommitteeModal">
        <i class="fas fa-plus me-1"></i> إضافة لجنة
    </button>
</div>

<div class="card card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>اسم اللجنة</th>
                        <th>الوصف</th>
                        <th>عدد الناخبين</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($committees as $committee)
                    <tr>
                        <td>{{ $loop->index + 1 }}</td>
                        <td class="fw-bold">{{ $committee->name }}</td>
                        <td>{{ $committee->description ?? '-' }}</td>
                        <td><span class="badge bg-info">{{ $committee->voters_count }}</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCommitteeModal{{ $committee->id }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('committees.destroy', $committee->id) }}" method="POST" class="d-inline">
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
                        <td colspan="5" class="text-center text-muted py-4">لا توجد لجان</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addCommitteeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة لجنة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('committees.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">اسم اللجنة</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الوصف</label>
                        <textarea name="description" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary-custom">إضافة</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($committees as $committee)
<div class="modal fade" id="editCommitteeModal{{ $committee->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تعديل لجنة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('committees.update', $committee->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">اسم اللجنة</label>
                        <input type="text" name="name" class="form-control" value="{{ $committee->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الوصف</label>
                        <textarea name="description" class="form-control">{{ $committee->description }}</textarea>
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