@extends('layouts.base')

@section('title', 'توزيع المهام')

@section('content')
<div class="page-header animate-in">
    <h3><i class="fas fa-tasks me-2 text-primary"></i> توزيع المهام</h3>
    <p class="text-muted">توزيع الناخبين على اللجان حسب العائلة والمركز انتخابي</p>
</div>

<div class="row g-4">
    <!-- Filters -->
    <div class="col-12">
        <div class="card card-custom">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">تصفية حسب العائلة</label>
                        <select name="family" class="form-select">
                            <option value="">كل العائلات</option>
                            @foreach($families as $family)
                            <option value="{{ $family->family_name }}" {{ request('family') == $family->family_name ? 'selected' : '' }}>
                                {{ $family->family_name }} ({{ $family->count }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">تصفية حسب المركز انتخابي</label>
                        <select name="center" class="form-select">
                            <option value="">كل المراكز</option>
                            @foreach($centers as $center)
                            <option value="{{ $center->electoral_center }}" {{ request('center') == $center->electoral_center ? 'selected' : '' }}>
                                {{ $center->electoral_center }} ({{ $center->count }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">تصفية حسب الحالة</label>
                        <select name="status" class="form-select">
                            <option value="">كل الحالات</option>
                            <option value="positive" {{ request('status') == 'positive' ? 'selected' : '' }}>مضمون</option>
                            <option value="uncertain" {{ request('status') == 'uncertain' ? 'selected' : '' }}>غير مضمون</option>
                            <option value="negative" {{ request('status') == 'negative' ? 'selected' : '' }}>لم ينخب</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary-custom w-100">
                            <i class="fas fa-filter me-1"></i> تصفية
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if($committees->count() > 0)
    <!-- Committee Selection -->
    <div class="col-lg-4">
        <div class="card card-custom">
            <div class="card-header-custom">
                <i class="fas fa-clipboard-check me-2"></i>
                توزيع الناخبين على لجنة
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('tasks.assign') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">اللجنة المكلفة</label>
                        <select name="committee_id" class="form-select" required>
                            <option value="">اختر لجنة...</option>
                            @foreach($committees as $committee)
                            <option value="{{ $committee->id }}">{{ $committee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">اختيار الناخبين:</label>
                        <div class="d-flex gap-2 mb-2">
                            <button type="button" class="btn btn-sm btn-outline-success" id="selectAllBtn">
                                <i class="fas fa-check-square"></i> تحديد الكل
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="deselectAllBtn">
                                <i class="fas fa-square"></i> إلغاء تحديد الكل
                            </button>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-1"></i>
                        عدد الناخبين المختارين: <span id="selectedCount" class="fw-bold">0</span> / {{ $voters->count() }}
                    </div>
                    
                    <button type="submit" class="btn btn-success-custom w-100">
                        <i class="fas fa-user-plus me-1"></i> توزيع على اللجنة
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Committees Stats -->
        <div class="card card-custom mt-4">
            <div class="card-header-custom">
                <i class="fas fa-chart-bar me-2"></i> اللجان الحالية
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($committees as $committee)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $committee->name }}</strong>
                                <br>
                                <small class="text-muted">{{ $committee->description ?? 'لا يوجد وصف' }}</small>
                            </div>
                            <span class="badge bg-primary rounded-pill">{{ $committee->voters_count }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    
    <!-- Voters List -->
    <div class="col-lg-8">
        <div class="card card-custom">
            <div class="card-header-custom">
                <i class="fas fa-users me-2"></i>
                الناخبين المتاحين للتوزيع
                <span class="badge bg-white text-primary ms-2">{{ $voters->count() }}</span>
                @if(request('family') || request('center') || request('status'))
                <span class="badge bg-warning text-dark ms-1"><i class="fas fa-filter"></i> مُصفين</span>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                    <table class="table table-custom table-hover mb-0">
                        <thead class="sticky-top bg-light">
                            <tr>
                                <th width="50">
                                    <input type="checkbox" class="form-check-input" id="headerCheckbox">
                                </th>
                                <th>الاسم بالكامل</th>
                                <th>اسم العائلة</th>
                                <th>المركز انتخابي</th>
                                <th>الحالة</th>
                                <th>اللجنة الحالية</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($voters as $voter)
                            <tr>
                                <td>
                                    <input type="checkbox" name="voter_ids[]" value="{{ $voter->id }}" class="form-check-input voter-checkbox">
                                </td>
                                <td class="fw-bold">{{ $voter->full_name }}</td>
                                <td>
                                    @if($voter->family_name)
                                    <span class="badge bg-primary bg-opacity-10 text-primary">{{ $voter->family_name }}</span>
                                    @else
                                    -
                                    @endif
                                </td>
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
                                    <span class="text-muted">غير موزع</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="fas fa-users fa-2x mb-3 d-block"></i>
                                    لا توجد ناخبين متاحين
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="col-12">
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            لا توجد لجان. الرجاء <a href="{{ route('committees.index') }}">إضافة لجان</a> أولاً
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.voter-checkbox:checked');
    document.getElementById('selectedCount').textContent = checkboxes.length;
    
    const headerCheckbox = document.getElementById('headerCheckbox');
    const allCheckboxes = document.querySelectorAll('.voter-checkbox');
    
    if (allCheckboxes.length === 0) {
        headerCheckbox.checked = false;
    } else if (checkboxes.length === allCheckboxes.length) {
        headerCheckbox.checked = true;
        headerCheckbox.indeterminate = false;
    } else if (checkboxes.length > 0) {
        headerCheckbox.checked = false;
        headerCheckbox.indeterminate = true;
    } else {
        headerCheckbox.checked = false;
        headerCheckbox.indeterminate = false;
    }
}

document.getElementById('selectAllBtn').addEventListener('click', function() {
    document.querySelectorAll('.voter-checkbox').forEach(cb => cb.checked = true);
    updateSelectedCount();
});

document.getElementById('deselectAllBtn').addEventListener('click', function() {
    document.querySelectorAll('.voter-checkbox').forEach(cb => cb.checked = false);
    updateSelectedCount();
});

document.getElementById('headerCheckbox').addEventListener('change', function() {
    document.querySelectorAll('.voter-checkbox').forEach(cb => cb.checked = this.checked);
    updateSelectedCount();
});

document.querySelectorAll('.voter-checkbox').forEach(cb => {
    cb.addEventListener('change', updateSelectedCount);
});

updateSelectedCount();
</script>
@endsection