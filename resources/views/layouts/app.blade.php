<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard</title>

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FONT -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- SUMMERNOTE CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-bs5.min.css" rel="stylesheet">

    <style>
        body {
            background: #f1f5f9;
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: #0f172a;
        }

        /* SIDEBAR */
        :root {
            --sb-bg: #0b1220;
            --sb-border: rgba(255, 255, 255, .07);
            --sb-text: #94a3b8;
            --sb-text-strong: #e2e8f0;
            --sb-hover: rgba(255, 255, 255, .06);
            --sb-active: rgba(59, 130, 246, .16);
            --sb-accent: #3b82f6;
        }

        .sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
            overflow-x: hidden;
            background: var(--sb-bg);
            border-right: 1px solid var(--sb-border);
            padding: 0;
            display: flex;
            flex-direction: column;
            color: var(--sb-text-strong);
            transition: all .3s ease;
            z-index: 1000;
        }

        .sidebar.hide {
            margin-left: -260px;
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, .12);
            border-radius: 6px;
        }

        /* Brand */
        .sb-brand {
            padding: 22px 22px 18px;
            border-bottom: 1px solid var(--sb-border);
        }

        .sb-brand-title {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            letter-spacing: .1px;
            color: #fff;
            line-height: 1.3;
        }

        .sb-brand-version {
            display: inline-block;
            margin-top: 6px;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: .4px;
            color: var(--sb-text);
        }

        .sb-nav {
            padding: 14px 12px;
        }

        .sb-label {
            padding: 14px 12px 6px;
            font-size: 10.5px;
            font-weight: 600;
            letter-spacing: 1.1px;
            text-transform: uppercase;
            color: #64748b;
        }

        .sb-label:first-child {
            padding-top: 4px;
        }

        .sidebar a,
        .menu-toggle {
            position: relative;
            display: flex;
            align-items: center;
            padding: 9px 12px;
            margin: 2px 0;
            border-radius: 8px;
            color: var(--sb-text);
            text-decoration: none;
            transition: background .2s ease, color .2s ease;
            font-size: 13.5px;
            font-weight: 500;
            cursor: pointer;
        }

        .sidebar a:hover,
        .menu-toggle:hover {
            background: var(--sb-hover);
            color: var(--sb-text-strong);
        }

        .sidebar a.active,
        .menu-toggle.active {
            background: var(--sb-active);
            color: #fff;
            font-weight: 600;
        }

        .sidebar a.active::before,
        .menu-toggle.active::before {
            content: "";
            position: absolute;
            left: 0;
            top: 8px;
            bottom: 8px;
            width: 3px;
            border-radius: 3px;
            background: var(--sb-accent);
        }

        /* DROPDOWN */
        .menu-group {
            margin-top: 2px;
        }

        .menu-toggle {
            justify-content: space-between;
        }

        .submenu {
            display: none;
            margin: 2px 0 4px 18px;
            padding-left: 10px;
            border-left: 1px solid var(--sb-border);
        }

        .submenu.show {
            display: block;
        }

        .submenu a {
            background: transparent;
            font-size: 13px;
            padding: 8px 12px;
            margin: 1px 0;
        }

        .submenu a:hover {
            background: var(--sb-hover);
        }

        .submenu a.active {
            background: var(--sb-active);
        }

        .submenu a.active::before {
            display: none;
        }

        /* panah dropdown (chevron CSS) */
        .arrow {
            width: 7px;
            height: 7px;
            border-right: 1.6px solid currentColor;
            border-bottom: 1.6px solid currentColor;
            transform: rotate(-45deg);
            transition: transform .25s ease;
            margin-right: 4px;
            opacity: .75;
        }

        .arrow.rotate {
            transform: rotate(45deg);
        }

        /* CONTENT */
        .content {
            margin-left: 260px;
            padding: 25px;
            min-height: 100vh;
            transition: all .3s ease;
        }

        .content.full {
            margin-left: 0;
        }

        /* BUTTON TOGGLE SIDEBAR */
        .toggle-sidebar {
            position: fixed;
            top: 12px;
            left: 272px;

            width: 38px;
            height: 38px;

            border: 1px solid #e2e8f0;
            border-radius: 10px;

            background: #ffffff;
            color: #334155;

            cursor: pointer;

            z-index: 1100;

            display: flex;
            align-items: center;
            justify-content: center;

            box-shadow: 0 1px 2px rgba(15, 23, 42, .06);

            transition: all .3s ease;
        }

        .toggle-sidebar svg {
            width: 18px;
            height: 18px;
        }

        .toggle-sidebar:hover {
            background: #f1f5f9;
            color: #0f172a;
        }


        /* ketika sidebar hide */
        .toggle-sidebar.move {
            left: 15px;
        }


        /* CONTENT ketika sidebar hide */
        .content.full {
            margin-left: 0;
        }

        /* CARD */
        .card-modern {
            background: white;
            border: 1px solid #e8edf3;
            border-radius: 14px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, .04);
        }

        /* TABLE */
        .table {
            background: white;
        }

        .table th {
            background: #f8fafc;
            font-size: 13px;
        }

        .nowrap-table th,
        .nowrap-table td {
            white-space: nowrap;
            font-size: 12px;
        }

        /* LOGOUT */
        .logout-wrapper {
            margin-top: auto;
            padding: 16px 16px 20px;
            border-top: 1px solid var(--sb-border);
        }

        .btn-logout {
            width: 100%;
            border: 1px solid rgba(248, 113, 113, .35);
            padding: 10px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 600;
            color: #fca5a5;
            background: transparent;
            transition: .2s;
        }

        .btn-logout:hover {
            background: rgba(239, 68, 68, .12);
            color: #fecaca;
            border-color: rgba(248, 113, 113, .6);
        }

        /* SUMMERNOTE CUSTOM STYLING */
        .note-toolbar {
            background: #1e293b !important;
            border-bottom: none !important;
        }

        .note-toolbar .btn,
        .note-toolbar .dropdown-toggle {
            color: #fff !important;
        }

        .note-toolbar .btn:hover,
        .note-toolbar .dropdown-toggle:hover {
            background: #334155 !important;
        }

        .note-editor {
            border-radius: 12px;
            overflow: hidden;
        }
    </style>
</head>

<body>
    <!-- BUTTON TOGGLE SIDEBAR -->
    <button id="btnSidebar" class="toggle-sidebar" onclick="toggleSidebar()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
            <path d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <!-- SIDEBAR -->
    <div class="sidebar" id="sidebar">

        <div class="sb-brand">
            @auth
            @php
            $title = match(auth()->user()->role) {
            'apo2' => 'Master Hub System E1',
            'finance' => 'Master Hub System E1',
            'admin' => 'Master Hub System',
            default => 'Master Hub System',
            };
            @endphp

            <h4 class="sb-brand-title">{{ $title }}</h4>
            @endauth
            <span class="sb-brand-version">Version 10.14</span>
        </div>

        <nav class="sb-nav">

            <div class="sb-label">Menu Utama</div>

            <a href="/" class="{{ request()->is('/') ? 'active' : '' }}">
                Dashboard
            </a>

            <a href="/upload" class="{{ request()->is('upload') ? 'active' : '' }}">
                Upload Sheet
            </a>

            <a href="/bulk" class="{{ request()->is('bulk') ? 'active' : '' }}">
                BC Email
            </a>

            <a href="/broadcast-all" class="{{ request()->is('broadcast-all*') ? 'active' : '' }}">
                Broadcast All
            </a>

            <a href="{{ route('queue-setting.edit') }}"
                class="{{ request()->is('queue-setting*') ? 'active' : '' }}">
                Edit Timer
            </a>

            @auth
            @php
            $role = auth()->user()->role;

            $masterOpen =
            request()->is('template-master*') ||
            request()->is('email-master*') ||
            request()->is('footer-master*') ||
            request()->is('sparepart-master*') ||
            request()->is('user-master*');
            @endphp

            {{-- Menu Master hanya untuk Admin & User --}}
            @if(in_array($role, ['admin','user','apo2','apo','finance']))

            <div class="sb-label">Data Master</div>

            <div class="menu-group">

                <div class="menu-toggle {{ $masterOpen ? 'active' : '' }}" onclick="toggleMaster()">
                    <span>Master</span>
                    <span id="masterArrow" class="arrow {{ $masterOpen ? 'rotate' : '' }}"></span>
                </div>

                <div id="masterMenu" class="submenu {{ $masterOpen ? 'show' : '' }}">

                    <a href="{{ route('sparepart-master.index') }}"
                        class="{{ request()->is('sparepart-master*') ? 'active' : '' }}">
                        Sparepart Master
                    </a>

                    <a href="/template-master"
                        class="{{ request()->is('template-master*') ? 'active' : '' }}">
                        Template
                    </a>

                    <a href="/email-master"
                        class="{{ request()->is('email-master*') ? 'active' : '' }}">
                        Email
                    </a>

                    <a href="/footer-master"
                        class="{{ request()->is('footer-master*') ? 'active' : '' }}">
                        Footer
                    </a>

                    @if(in_array($role, ['master','admin','apo','apo2','finance']))
                    <a href="/user-master"
                        class="{{ request()->is('user-master*') ? 'active' : '' }}">
                        Akun
                    </a>
                    @endif

                </div>

            </div>
            @endif
            @endauth

        </nav>

        @auth
        <div class="logout-wrapper">
            <form action="/logout" method="POST">
                @csrf
                <button class="btn-logout" onclick="return confirm('Yakin ingin logout?')">
                    Logout
                </button>
            </form>
        </div>
        @endauth
    </div>

    <!-- CONTENT -->
    <div class="content" id="content">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm"
            style="border-radius:12px; font-weight:500;">
            {!! session('success') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm"
            style="border-radius:12px; font-weight:500;">
            {!! session('error') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="card-modern">
            @yield('content')
        </div>
    </div>

    <!-- JSCRIPTS -->
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Summernote JS -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-bs5.min.js"></script>

    <!-- Custom Script -->
    <script>
        function toggleMaster() {
            let menu = document.getElementById('masterMenu');
            let arrow = document.getElementById('masterArrow');

            menu.classList.toggle('show');
            arrow.classList.toggle('rotate');
        }

        function toggleSidebar() {

            let sidebar = document.getElementById('sidebar');
            let content = document.getElementById('content');
            let button = document.getElementById('btnSidebar');


            sidebar.classList.toggle('hide');
            content.classList.toggle('full');
            button.classList.toggle('move');


        }
    </script>
</body>

</html>