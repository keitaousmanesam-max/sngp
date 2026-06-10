<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Espace Fournisseur') — SNGP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --f-primary:   #1E40AF;
            --f-secondary: #3B82F6;
            --f-accent:    #60A5FA;
            --f-dark:      #1E3A8A;
            --sidebar-w:   260px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #EFF6FF; overflow-x: hidden; }

        /* ── NAVBAR ── */
        .f-navbar {
            height: 64px;
            background: linear-gradient(135deg, var(--f-dark) 0%, var(--f-primary) 60%, var(--f-secondary) 100%);
            box-shadow: 0 2px 12px rgba(30,64,175,0.25);
            position: sticky; top: 0; z-index: 1050;
            display: flex; align-items: center; padding: 0 24px;
            gap: 16px;
        }
        .f-navbar .brand { display: flex; align-items: center; gap: 10px; color: white; font-size: 18px; font-weight: 800; letter-spacing: 1px; flex-shrink: 0; }
        .f-navbar .brand span { opacity: .75; font-weight: 400; font-size: 13px; margin-left: 4px; }
        .f-navbar .separator { width: 1px; height: 28px; background: rgba(255,255,255,.25); margin: 0 4px; flex-shrink: 0; }
        .f-navbar .company-name { color: white; font-size: 14px; font-weight: 600; opacity: .9; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .f-navbar .ms-auto { margin-left: auto !important; }
        .f-navbar .nav-btn { background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.25); color: white; border-radius: 10px; padding: 7px 14px; font-size: 13px; font-weight: 500; text-decoration: none; display: flex; align-items: center; gap: 7px; transition: background .2s; cursor: pointer; white-space: nowrap; }
        .f-navbar .nav-btn:hover { background: rgba(255,255,255,.22); color: white; }
        .f-navbar .nav-btn.danger { background: rgba(239,68,68,.15); border-color: rgba(239,68,68,.35); }
        .f-navbar .nav-btn.danger:hover { background: rgba(239,68,68,.3); }
        .f-navbar .toggle-btn { background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.2); color: white; border-radius: 8px; width: 40px; height: 40px; display: none; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; }
        @media (max-width:992px) { .f-navbar .toggle-btn { display: flex; } }

        /* ── SIDEBAR ── */
        .f-sidebar {
            position: fixed; top: 64px; left: 0;
            width: var(--sidebar-w); height: calc(100vh - 64px);
            background: white; box-shadow: 2px 0 12px rgba(30,64,175,.07);
            overflow-y: auto; z-index: 1000; transition: left .3s;
            display: flex; flex-direction: column;
        }
        .f-sidebar::-webkit-scrollbar { width: 4px; }
        .f-sidebar::-webkit-scrollbar-thumb { background: #BFDBFE; border-radius: 2px; }

        .sidebar-profile {
            padding: 20px 20px 16px;
            background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%);
            border-bottom: 1px solid #BFDBFE;
        }
        .profile-avatar {
            width: 52px; height: 52px; border-radius: 14px;
            background: linear-gradient(135deg, var(--f-dark), var(--f-secondary));
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 20px; font-weight: 800;
            margin-bottom: 10px; box-shadow: 0 4px 12px rgba(30,64,175,.25);
        }
        .profile-name { font-size: 14px; font-weight: 700; color: #1E3A8A; }
        .profile-role { font-size: 11px; color: #3B82F6; font-weight: 600; background: #DBEAFE; padding: 2px 8px; border-radius: 10px; display: inline-block; margin-top: 3px; }

        .sidebar-menu { padding: 12px 0; flex: 1; }
        .menu-section-title { padding: 14px 20px 6px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #93C5FD; }
        .menu-item {
            display: flex; align-items: center; gap: 12px;
            padding: 11px 20px; color: #374151; text-decoration: none;
            font-size: 14px; font-weight: 500; border-left: 3px solid transparent;
            transition: all .2s;
        }
        .menu-item i { width: 20px; font-size: 15px; color: #93C5FD; flex-shrink: 0; }
        .menu-item:hover { background: #EFF6FF; color: var(--f-primary); border-left-color: var(--f-secondary); }
        .menu-item:hover i { color: var(--f-secondary); }
        .menu-item.active { background: #DBEAFE; color: var(--f-dark); border-left-color: var(--f-dark); font-weight: 700; }
        .menu-item.active i { color: var(--f-dark); }
        .menu-badge { margin-left: auto; background: #EF4444; color: white; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 700; }

        .sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid #DBEAFE;
            font-size: 11px; color: #93C5FD; text-align: center;
        }

        /* ── MAIN ── */
        .f-main { margin-left: var(--sidebar-w); padding: 28px 32px; min-height: calc(100vh - 64px); transition: margin .3s; }
        @media (max-width:992px) {
            .f-sidebar { left: calc(-1 * var(--sidebar-w)); }
            .f-sidebar.show { left: 0; }
            .f-main { margin-left: 0; padding: 20px 16px; }
            .f-overlay { display: none; position: fixed; inset: 64px 0 0 0; background: rgba(0,0,0,.4); z-index: 999; }
            .f-overlay.show { display: block; }
        }
        @media (max-width:576px) { .f-main { padding: 14px 12px; } }

        /* ── ALERTS ── */
        .f-alert { border-radius: 12px; border: none; padding: 14px 18px; font-size: 14px; }
    </style>
    @stack('styles')
</head>
<body>

<!-- NAVBAR -->
<nav class="f-navbar">
    <button class="toggle-btn" id="sidebarToggle"><i class="fas fa-bars"></i></button>
    <div class="brand">
        <i class="fas fa-pills"></i> SNGP <span>Fournisseur</span>
    </div>
    <div class="separator d-none d-md-block"></div>
    @auth
    <div class="company-name d-none d-md-block">
        @php $fnom = \App\Models\Fournisseur::where('email', auth()->user()->email)->value('nom'); @endphp
        {{ $fnom ?? auth()->user()->prenom . ' ' . auth()->user()->nom }}
    </div>
    @endauth
    <div class="ms-auto d-flex align-items-center gap-2">
        @php
            $nbNouvelles = 0;
            try {
                $fournisseur = \App\Models\Fournisseur::where('email', auth()->user()->email)->first();
                if ($fournisseur) $nbNouvelles = $fournisseur->commandes()->where('statut','envoyee')->count();
            } catch(\Exception $e) {}
        @endphp
        @if($nbNouvelles > 0)
        <a href="{{ route('fournisseur.espace.commandes') }}" class="nav-btn d-none d-sm-flex">
            <i class="fas fa-bell"></i>
            <span style="background:#EF4444;color:white;padding:1px 7px;border-radius:10px;font-size:11px;font-weight:700;">{{ $nbNouvelles }}</span>
        </a>
        @endif
        <a href="{{ route('fournisseur.espace.profil') }}" class="nav-btn d-none d-sm-flex">
            <i class="fas fa-user-circle"></i>
            <span class="d-none d-lg-inline">{{ auth()->user()->prenom }}</span>
        </a>
        <form method="POST" action="{{ route('logout') }}" class="d-inline">
            @csrf
            <button type="submit" class="nav-btn danger">
                <i class="fas fa-sign-out-alt"></i>
                <span class="d-none d-md-inline">Déconnexion</span>
            </button>
        </form>
    </div>
</nav>

<!-- SIDEBAR -->
<aside class="f-sidebar" id="fSidebar">
    <div class="sidebar-profile">
        <div class="profile-avatar">
            {{ strtoupper(substr(auth()->user()->prenom ?? 'F', 0, 1)) }}{{ strtoupper(substr(auth()->user()->nom ?? '', 0, 1)) }}
        </div>
        <div class="profile-name">{{ auth()->user()->prenom }} {{ auth()->user()->nom }}</div>
        <div class="profile-role"><i class="fas fa-building me-1"></i>Fournisseur Agréé</div>
    </div>

    <div class="sidebar-menu">
        <div class="menu-section-title">Navigation</div>

        <a href="{{ route('fournisseur.espace.dashboard') }}"
           class="menu-item {{ request()->routeIs('fournisseur.espace.dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt"></i><span>Tableau de bord</span>
        </a>

        <a href="{{ route('fournisseur.espace.commandes') }}"
           class="menu-item {{ request()->routeIs('fournisseur.espace.commandes') || request()->routeIs('fournisseur.espace.commande.*') ? 'active' : '' }}">
            <i class="fas fa-file-invoice"></i><span>Mes Commandes</span>
            @if($nbNouvelles > 0)
            <span class="menu-badge">{{ $nbNouvelles }}</span>
            @endif
        </a>

        <div class="menu-section-title" style="margin-top:8px;">Compte</div>

        <a href="{{ route('fournisseur.espace.profil') }}"
           class="menu-item {{ request()->routeIs('fournisseur.espace.profil') ? 'active' : '' }}">
            <i class="fas fa-user-circle"></i><span>Mon Profil</span>
        </a>

        <a href="{{ route('password.modifier.form') }}" class="menu-item">
            <i class="fas fa-key"></i><span>Mot de passe</span>
        </a>
    </div>

    <div class="sidebar-footer">
        SNGP &mdash; Rép. de Guinée<br>
        Ministère de la Santé
    </div>
</aside>

<!-- Overlay mobile -->
<div class="f-overlay" id="fOverlay"></div>

<!-- MAIN CONTENT -->
<main class="f-main">

    @if(session('success'))
    <div class="alert f-alert alert-success alert-dismissible fade show mb-4">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert f-alert alert-danger alert-dismissible fade show mb-4">
        <i class="fas fa-times-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('warning'))
    <div class="alert f-alert alert-warning alert-dismissible fade show mb-4">
        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const toggle  = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('fSidebar');
    const overlay = document.getElementById('fOverlay');
    if (toggle) {
        toggle.addEventListener('click', function () {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        });
    }
    if (overlay) {
        overlay.addEventListener('click', function () {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        });
    }
    setTimeout(function () {
        document.querySelectorAll('.alert-dismissible').forEach(function (el) {
            new bootstrap.Alert(el).close();
        });
    }, 5000);
</script>
@stack('scripts')
</body>
</html>
