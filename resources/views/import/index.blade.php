@extends('layouts.base')

@section('title', 'استيراد Excel')

@section('content')
<div class="page-header animate-in">
    <h3><i class="fas fa-file-import me-2 text-primary"></i> استيراد من Excel</h3>
    <p class="text-muted">قم برفع ملف Excel يحتوي على بيانات الناخبين</p>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card card-custom">
            <div class="card-header-custom">
                <i class="fas fa-upload me-2"></i> رفع ملف
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('import.import') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">اختر ملف Excel</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                    </div>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="fas fa-upload me-1"></i> استيراد
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card card-custom">
            <div class="card-header-custom">
                <i class="fas fa-download me-2"></i> تحميل نموذج
            </div>
            <div class="card-body">
                <p>قم بتحميل نموذج Excel جاهز لاستيراد البيانات</p>
                <a href="{{ route('import.template') }}" class="btn btn-success-custom">
                    <i class="fas fa-file-excel me-1"></i> تحميل النموذج
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card card-custom mt-4">
    <div class="card-header-custom">
        <i class="fas fa-info-circle me-2"></i> تعليمات
    </div>
    <div class="card-body">
        <ul>
            <li>يجب أن يحتوي الملف على الأعمدة التالية:</li>
            <ul>
                <li>الاسم بالكامل (مطلوب)</li>
                <li>اسم العائلة (اختياري)</li>
                <li>الرمز انتخابي (اختياري)</li>
                <li>المركز انتخابي (اختياري)</li>
            </ul>
            <li>الصف الأول يجب أن يكون عنوان للأعمدة</li>
            <li>Supported formats: .xlsx, .xls</li>
        </ul>
    </div>
</div>
@endsection