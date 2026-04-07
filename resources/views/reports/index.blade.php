@extends('layouts.base')

@section('title', 'التقارير')

@section('content')
<div class="page-header animate-in">
    <h3><i class="fas fa-chart-bar me-2 text-primary"></i> التقارير</h3>
</div>

<!-- Families Report -->
<div class="card card-custom mb-4">
    <div class="card-header-custom">
        <i class="fas fa-users me-2"></i>
        تقارير حسب العائلة
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-custom table-hover">
                <thead>
                    <tr>
                        <th>العائلة</th>
                        <th>الإجمالي</th>
                        <th>مضمون</th>
                        <th>غير مضمون</th>
                        <th>لم ينخب</th>
                        <th>نسبة النجاح</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($familiesStats as $family)
                    <tr>
                        <td class="fw-bold">{{ $family->family_name }}</td>
                        <td>{{ $family->total }}</td>
                        <td><span class="badge bg-success">{{ $family->positive }}</span></td>
                        <td><span class="badge bg-warning">{{ $family->uncertain }}</span></td>
                        <td><span class="badge bg-danger">{{ $family->negative }}</span></td>
                        <td>
                            <span class="badge bg-{{ $family->success_rate >= 50 ? 'success' : 'warning' }}">
                                {{ $family->success_rate }}%
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center">لا توجد بيانات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Centers Report -->
<div class="card card-custom mb-4">
    <div class="card-header-custom">
        <i class="fas fa-school me-2"></i>
        تقارير حسب المركز انتخابي
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-custom table-hover">
                <thead>
                    <tr>
                        <th>المركز انتخابي</th>
                        <th>الإجمالي</th>
                        <th>مضمون</th>
                        <th>غير مضمون</th>
                        <th>لم ينخب</th>
                        <th>نسبة النجاح</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($centersStats as $center)
                    <tr>
                        <td class="fw-bold">{{ $center->electoral_center }}</td>
                        <td>{{ $center->total }}</td>
                        <td><span class="badge bg-success">{{ $center->positive }}</span></td>
                        <td><span class="badge bg-warning">{{ $center->uncertain }}</span></td>
                        <td><span class="badge bg-danger">{{ $center->negative }}</span></td>
                        <td>
                            <span class="badge bg-{{ $center->success_rate >= 50 ? 'success' : 'warning' }}">
                                {{ $center->success_rate }}%
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center">لا توجد بيانات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Committees Report -->
<div class="card card-custom">
    <div class="card-header-custom">
        <i class="fas fa-clipboard-list me-2"></i>
        تقارير حسب اللجنة
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-custom table-hover">
                <thead>
                    <tr>
                        <th>اللجنة</th>
                        <th>الناخبين المعينين</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($committeesStats as $committee)
                    <tr>
                        <td class="fw-bold">{{ $committee->name }}</td>
                        <td><span class="badge bg-primary">{{ $committee->voters_count }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="text-center">لا توجد لجان</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection