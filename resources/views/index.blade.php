@extends('layouts.base')

@section('title', 'الرئيسية')

@section('content')
<div class="page-header animate-in">
    <h3>
        <i class="fas fa-chart-line me-2 text-primary"></i>
        لوحة التحكم - متابعة ميدان انتخبات البلدية 2026
    </h3>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card card-custom">
            <div class="card-body text-center">
                <i class="fas fa-users fa-3x text-primary mb-3"></i>
                <h4 class="mb-0">{{ $totalVoters }}</h4>
                <p class="text-muted">إجمالي الناخبين</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom">
            <div class="card-body text-center">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h4 class="mb-0">{{ $positiveCount }}</h4>
                <p class="text-muted">مضمون</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom">
            <div class="card-body text-center">
                <i class="fas fa-question-circle fa-3x text-warning mb-3"></i>
                <h4 class="mb-0">{{ $uncertainCount }}</h4>
                <p class="text-muted">غير مضمون</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom">
            <div class="card-body text-center">
                <i class="fas fa-times-circle fa-3x text-danger mb-3"></i>
                <h4 class="mb-0">{{ $negativeCount }}</h4>
                <p class="text-muted">لم ينخب</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Charts -->
    <div class="col-lg-6">
        <div class="card card-custom">
            <div class="card-header-custom">
                <i class="fas fa-chart-pie me-2"></i>
                النسبة الإجمالية
            </div>
            <div class="card-body">
                <canvas id="statusChart" height="250"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card card-custom">
            <div class="card-header-custom">
                <i class="fas fa-chart-bar me-2"></i>
                الحالة حسب الناخبين
            </div>
            <div class="card-body">
                <canvas id="barChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Families Table -->
<div class="row g-4 mt-2">
    <div class="col-12">
        <div class="card card-custom">
            <div class="card-header-custom">
                <i class="fas fa-users me-2"></i>
                إحصائيات العائلات
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
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">لا توجد بيانات</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Centers Table -->
<div class="row g-4 mt-2">
    <div class="col-12">
        <div class="card card-custom">
            <div class="card-header-custom">
                <i class="fas fa-school me-2"></i>
                إحصائيات المراكز选举ية
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
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">لا توجد بيانات</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Committees Table -->
<div class="row g-4 mt-2">
    <div class="col-12">
        <div class="card card-custom">
            <div class="card-header-custom">
                <i class="fas fa-clipboard-list me-2"></i>
                اللجان
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-custom table-hover">
                        <thead>
                            <tr>
                                <th>اللجنة</th>
                                <th>الناخبين المعينين</th>
                                <th>مضمون</th>
                                <th>غير مضمون</th>
                                <th>لم ينخب</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($committees as $committee)
                            <tr>
                                <td class="fw-bold">{{ $committee->name }}</td>
                                <td>{{ $committee->voters_count }}</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
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
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Pie Chart
    const statusChart = new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: ['مضمون', 'غير مضمون', 'لم ينخب', 'قيد الانتظار'],
            datasets: [{
                data: [{{ $positiveCount }}, {{ $uncertainCount }}, {{ $negativeCount }}, {{ $pendingCount }}],
                backgroundColor: ['#28a745', '#f59e0b', '#dc2626', '#0ea5e9']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // Bar Chart
    const barChart = new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: {
            labels: ['مضمون', 'غير مضمون', 'لم ينخب', 'قيد الانتظار'],
            datasets: [{
                label: 'عدد الناخبين',
                data: [{{ $positiveCount }}, {{ $uncertainCount }}, {{ $negativeCount }}, {{ $pendingCount }}],
                backgroundColor: ['#28a745', '#f59e0b', '#dc2626', '#0ea5e9']
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>
@endsection