<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar">
            <a class="brand" href="{{ route('dashboard') }}">
                <span class="brand-mark"><i class="bi bi-archive-fill"></i></span>
                <span>
                    <strong>e-Arsip Kontrak</strong>
                    <small>Divisi Kontrak</small>
                </span>
            </a>

            <nav class="nav flex-column sidebar-nav">
                <div class="nav-section">Menu Utama</div>
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="bi bi-grid-1x2"></i> Dashboard
                </a>
                <a class="nav-link {{ request()->routeIs('projects.*') ? 'active' : '' }}" href="{{ route('projects.index') }}">
                    <i class="bi bi-buildings"></i> Proyek
                </a>
                <a class="nav-link {{ request()->routeIs('documents.*') ? 'active' : '' }}" href="{{ route('documents.index') }}">
                    <i class="bi bi-folder2-open"></i> Dokumen Arsip
                </a>

                @if (auth()->user()->isAdmin() || auth()->user()->isLegal())
                    <div class="nav-section">Pengaturan Arsip</div>
                    <a class="nav-link {{ request()->routeIs('classifications.*') ? 'active' : '' }}" href="{{ route('classifications.index') }}">
                        <i class="bi bi-diagram-3"></i> Klasifikasi
                    </a>
                @endif

                @if (auth()->user()->isAdmin())
                    <div class="nav-section">Administrasi</div>
                    <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                        <i class="bi bi-people"></i> Pengguna
                    </a>
                @endif
            </nav>

            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <span class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    <div>
                        <div class="name">{{ auth()->user()->name }}</div>
                        <div class="role">{{ auth()->user()->roleLabel() }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="logout-link" type="submit">
                        <i class="bi bi-box-arrow-left"></i> Keluar
                    </button>
                </form>
            </div>
        </aside>

        <div class="app-main">
            <header class="topbar">
                <div>
                    <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
                    <p class="page-subtitle">@yield('page-subtitle', 'Sistem Manajemen Arsip Kontrak Konstruksi')</p>
                </div>
                <div class="topbar-user">
                    <div class="text-end d-none d-sm-block">
                        <div class="user-name">{{ auth()->user()->name }}</div>
                        <div class="user-role">{{ auth()->user()->isAdmin() ? 'Akses penuh' : auth()->user()->roleLabel() }}</div>
                    </div>
                    <span class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                </div>
            </header>

            <main class="content">
                @include('partials.flash')
                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
