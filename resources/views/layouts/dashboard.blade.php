<!DOCTYPE html>
<html>
<head>
    <title>Web Guru</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .sidebar {
            background-color: rgb(24, 105, 4);
            color: white;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 0 0 12px rgba(0, 0, 0, 1);
        }

        .sidebar a {
            color: white;
            display: block;
            padding: 10px 20px;
            font-size: 16px;
            text-decoration: none;
            border-radius: 6px;
            margin-bottom: 6px;
            transition: background 0.3s;
        }

        .sidebar a:hover {
            background: linear-gradient(to right, rgb(60, 180, 70), rgb(140, 250, 140));
        }

        .sidebar a.active {
            background: linear-gradient(to right, rgb(25, 190, 20), rgb(116, 255, 109));
            font-weight: bold;
        }

        .sidebar-header {
            background-color: rgb(16, 70, 2);
            padding: 16px 20px;
        }

        .offcanvas.sidebar {
            max-width: 250px !important;
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.2);
        }

        .offcanvas-header {
            background-color: rgb(16, 70, 2);
            color: white;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .offcanvas-body a:hover {
            background: linear-gradient(to right, rgb(60, 180, 70), rgb(140, 250, 140));
        }

        .offcanvas-body a.active {
            background: linear-gradient(to right, rgb(25, 190, 20), rgb(116, 255, 109));
            font-weight: bold;
        }

        .mobile-topbar {
            background-color: rgb(16, 70, 2);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0">
        <!-- Topbar for mobile -->
        <div class="d-md-none mobile-topbar p-3 d-flex justify-content-between align-items-center">
            <button class="btn btn-outline-light btn-sm" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar">
                <i class="bi bi-list fs-5"></i>
            </button>
            <span class="fw-semibold fs-5">Guru Panel</span>
        </div>

        <div class="row g-0">
            <!-- Mobile Offcanvas Sidebar -->
            <div class="offcanvas offcanvas-start sidebar d-md-none" tabindex="-1" id="offcanvasSidebar">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title">📚 Guru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-house-door-fill me-2"></i>Dashboard
                    </a>
                    <a href="{{ route('nilai.create') }}" class="{{ request()->routeIs('nilai.create') ? 'active' : '' }}">
                        <i class="bi bi-pencil-square me-2"></i>Input Nilai
                    </a>
                    <a href="{{ route('absensi.create') }}" class="{{ request()->routeIs('absensi.create') ? 'active' : '' }}">
                        <i class="bi bi-calendar-plus me-2"></i>Input Absensi
                    </a>
                </div>
            </div>

            <!-- Desktop Sidebar -->
            <div class="col-md-3 col-lg-2 d-none d-md-block sidebar">
                <div class="sidebar-header">
                    <h5 class="fw-bold mb-0">📚 Guru</h5>
                </div>
                <div class="p-3">
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-house-door-fill me-2"></i>Dashboard
                    </a>
                    <a href="{{ route('nilai.create') }}" class="{{ request()->routeIs('nilai.create') ? 'active' : '' }}">
                        <i class="bi bi-pencil-square me-2"></i>Input Nilai
                    </a>
                    <a href="{{ route('absensi.create') }}" class="{{ request()->routeIs('absensi.create') ? 'active' : '' }}">
                        <i class="bi bi-calendar-plus me-2"></i>Input Absensi
                    </a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 py-4 px-4 px-md-5">
                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
