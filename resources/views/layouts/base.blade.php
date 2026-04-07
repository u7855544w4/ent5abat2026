<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'متابعة ميدان انتخبات البلدية 2026')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #1e3a5f;
            --secondary: #2c5282;
            --success: #28a745;
            --warning: #f59e0b;
            --danger: #dc2626;
            --info: #0ea5e9;
            --light: #f8fafc;
            --dark: #1e293b;
        }
        body {
            font-family: 'Cairo', sans-serif;
            background: var(--light);
        }
        .navbar {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
        }
        .card-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.07);
            transition: transform 0.2s;
        }
        .card-custom:hover {
            transform: translateY(-2px);
        }
        .card-header-custom {
            background: var(--primary);
            color: white;
            padding: 15px 20px;
            border-radius: 12px 12px 0 0;
            font-weight: 600;
        }
        .btn-primary-custom {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
        }
        .btn-primary-custom:hover {
            background: var(--secondary);
            color: white;
        }
        .btn-success-custom {
            background: var(--success);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        .status-positive { background: #d1fae5; color: #065f46; }
        .status-uncertain { background: #fef3c7; color: #92400e; }
        .status-negative { background: #fee2e2; color: #991b1b; }
        .status-pending { background: #e0f2fe; color: #075985; }
        .table-custom th {
            background: var(--primary);
            color: white;
            font-weight: 600;
        }
        .page-header {
            margin-bottom: 20px;
        }
        .form-control-custom {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px;
        }
        .form-control-custom:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(30,58,95,0.1);
        }
        .animate-in {
            animation: fadeIn 0.3s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
    @yield('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="fas fa-vote-yea me-2"></i>
                متابعة ميدان انتخبات البلدية 2026
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}">
                            <i class="fas fa-home me-1"></i> الرئيسية
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('voters.index') }}">
                            <i class="fas fa-users me-1"></i> الناخبين
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('import.index') }}">
                            <i class="fas fa-file-import me-1"></i> استيراد Excel
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('families.index') }}">
                            <i class="fas fa-users-rectangle me-1"></i> العائلات
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('committees.index') }}">
                            <i class="fas fa-clipboard-list me-1"></i> اللجان
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('tasks.index') }}">
                            <i class="fas fa-tasks me-1"></i> توزيع المهام
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('reports.index') }}">
                            <i class="fas fa-chart-bar me-1"></i> التقارير
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>