@extends('layouts.base')

@section('title', 'الناخبين')

@section('content')
<div class="page-header animate-in d-flex justify-content-between align-items-center">
    <h3><i class="fas fa-users me-2 text-primary"></i> الناخبين</h3>
    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addVoterModal">
        <i class="fas fa-plus me-1"></i> إضافة ناخب
    </button>
</div>

<!-- Filters -->
<div class="card card-custom mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="البحث بالاسم أو الرمز" value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="family" class="form-select">
                    <option value="">كل العائلات</option>
                    @foreach($families as $family)
                    <option value="{{ $family }}" {{ request('family') == $family ? 'selected' : '' }}>{{ $family }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="center" class="form-select">
                    <option value="">كل المراكز</option>
                    @foreach($centers as $center)
                    <option value="{{ $center }}" {{ request('center') == $center ? 'selected' : '' }}>{{ $center }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">كل الحالات</option>
                    <option value="positive" {{ request('status') == 'positive' ? 'selected' : '' }}>مضمون</option>
                    <option value="uncertain" {{ request('status') == 'uncertain' ? 'selected' : '' }}>غير مضمون</option>
                    <option value="negative" {{ request('status') == 'negative' ? 'selected' : '' }}>لم ينخب</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary-custom">
                    <i class="fas fa-filter me-1"></i> بحث
                </button>
                <a href="{{ route('voters.index') }}" class="btn btn-outline-secondary">إعادة تعيين</a>
            </div>
        </form>
    </div>
</div>

<!-- Voters Table -->
<div class="card card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الاسم بالكامل</th>
                        <th>اسم العائلة</th>
                        <th>الرمز انتخابي</th>
                        <th>المركز انتخابي</th>
                        <th>الحالة</th>
                        <th>اللجنة</th>
                        <th>المتابع</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($voters as $voter)
                    <tr>
                        <td>{{ $voters->firstItem() + $loop->index }}</td>
                        <td class="fw-bold">{{ $voter->full_name }}</td>
                        <td>
                            @if($voter->family_name)
                            <span class="badge bg-primary bg-opacity-10 text-primary">{{ $voter->family_name }}</span>
                            @else
                            -
                            @endif
                        </td>
                        <td>{{ $voter->electoral_code ?? '-' }}</td>
                        <td>{{ $voter->electoral_center ?? '-' }}</td>
                        <td>
                            @if($voter->status == 'positive')
                            <span class="status-badge status-positive">مضمون</span>
                            @elseif($voter->status == 'uncertain')
                            <span class="status-badge status-uncertain">غير مضمون</span>
                            @elseif($voter->status == 'negative')
                            <span class="status-badge status-negative">لم ينخب</span>
                            @else
                            <span class="status-badge status-pending">قيد الانتظار</span>
                            @endif
                        </td>
                        <td>
                            @if($voter->committee)
                            <span class="badge bg-info">{{ $voter->committee->name }}</span>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $voter->follower_name ?? '-' }}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editVoterModal{{ $voter->id }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('voters.destroy', $voter->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('هل أنت متأكد من الحذف؟')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editVoterModal{{ $voter->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">تعديل ناخب</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('voters.update', $voter->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">الاسم بالكامل</label>
                                            <input type="text" name="full_name" class="form-control" value="{{ $voter->full_name }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">اسم العائلة</label>
                                            <input type="text" name="family_name" class="form-control" value="{{ $voter->family_name }}">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">الرمز انتخابي</label>
                                            <input type="text" name="electoral_code" class="form-control" value="{{ $voter->electoral_code }}">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">المركز انتخابي</label>
                                            <input type="text" name="electoral_center" class="form-control" value="{{ $voter->electoral_center }}">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">الحالة</label>
                                            <select name="status" class="form-select">
                                                <option value="pending" {{ $voter->status == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                                <option value="positive" {{ $voter->status == 'positive' ? 'selected' : '' }}>مضمون</option>
                                                <option value="uncertain" {{ $voter->status == 'uncertain' ? 'selected' : '' }}>غير مضمون</option>
                                                <option value="negative" {{ $voter->status == 'negative' ? 'selected' : '' }}>لم ينخب</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">اسم المتابع</label>
                                            <input type="text" name="follower_name" class="form-control" value="{{ $voter->follower_name }}">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">ملاحظات</label>
                                            <textarea name="notes" class="form-control">{{ $voter->notes }}</textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary-custom">حفظ</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-5">
                            <i class="fas fa-users fa-2x mb-3 d-block"></i>
                            لا توجد ناخبين
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $voters->links() }}
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addVoterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة ناخب جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('voters.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">الاسم بالكامل *</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">اسم العائلة</label>
                        <input type="text" name="family_name" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الرمز انتخابي</label>
                        <input type="text" name="electoral_code" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">المركز انتخابي</label>
                        <input type="text" name="electoral_center" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الحالة</label>
                        <select name="status" class="form-select">
                            <option value="pending">قيد الانتظار</option>
                            <option value="positive">مضمون</option>
                            <option value="uncertain">غير مضمون</option>
                            <option value="negative">لم ينخب</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary-custom">إضافة</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection