<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - SNGP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1E3A8A;
            --secondary-color: #3B82F6;
            --success-color: #10B981;
            --danger-color: #EF4444;
            --warning-color: #F59E0B;
            --info-color: #3B82F6;
            --sidebar-width: 260px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #F3F4F6; overflow-x: hidden; }
        .navbar-custom { background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); box-shadow: 0 2px 8px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 1000; height: 60px; }
        .navbar-brand { font-size: 24px; font-weight: 700; color: white !important; display: flex; align-items: center; }
        .navbar-brand i { font-size: 28px; margin-right: 10px; }
        .user-dropdown .dropdown-toggle { background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.3); color: white; padding: 8px 16px; border-radius: 8px; transition: all 0.3s; }
        .user-dropdown .dropdown-toggle:hover { background: rgba(255,255,255,0.2); }
        .notifications-btn { position: relative; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.3); color: white; width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; transition: all 0.3s; }
        .notifications-btn:hover { background: rgba(255,255,255,0.2); color: white; }
        .notifications-badge { position: absolute; top: -5px; right: -5px; background: var(--danger-color); color: white; border-radius: 10px; padding: 2px 6px; font-size: 10px; font-weight: 700; }
        .sidebar { position: fixed; top: 60px; left: 0; width: var(--sidebar-width); height: calc(100vh - 60px); background: white; box-shadow: 2px 0 8px rgba(0,0,0,0.05); overflow-y: auto; transition: all 0.3s; z-index: 999; }
        .sidebar::-webkit-scrollbar { width: 6px; }
        .sidebar::-webkit-scrollbar-track { background: #f1f1f1; }
        .sidebar::-webkit-scrollbar-thumb { background: #ccc; border-radius: 3px; }
        .sidebar-header { padding: 20px; background: linear-gradient(135deg, #F9FAFB 0%, #F3F4F6 100%); border-bottom: 1px solid #E5E7EB; }
        .user-profile { display: flex; align-items: center; }
        .user-avatar { width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); display: flex; align-items: center; justify-content: center; color: white; font-size: 18px; font-weight: 700; margin-right: 12px; flex-shrink: 0; }
        .user-info h6 { margin: 0; font-size: 14px; font-weight: 600; color: #1F2937; }
        .user-info small { color: #6B7280; font-size: 12px; }
        .sidebar-menu { padding: 10px 0; }
        .menu-section { padding: 12px 20px 8px; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #9CA3AF; letter-spacing: 0.5px; }
        .menu-item { display: flex; align-items: center; padding: 12px 20px; color: #374151; text-decoration: none; transition: all 0.2s; border-left: 3px solid transparent; font-size: 14px; }
        .menu-item i { width: 24px; margin-right: 12px; font-size: 16px; }
        .menu-item:hover { background: #F9FAFB; color: var(--secondary-color); border-left-color: var(--secondary-color); }
        .menu-item.active { background: #EFF6FF; color: var(--secondary-color); border-left-color: var(--secondary-color); font-weight: 600; }
        .menu-badge { margin-left: auto; background: var(--danger-color); color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; }
        .main-content { margin-left: var(--sidebar-width); padding: 30px; min-height: calc(100vh - 60px); transition: all 0.3s; }
        .page-title { font-size: 28px; font-weight: 700; color: var(--primary-color); margin-bottom: 8px; }
        .page-subtitle { color: #6B7280; font-size: 14px; }
        @media (max-width: 992px) {
            .sidebar { left: calc(-1 * var(--sidebar-width)); }
            .sidebar.show { left: 0; }
            .main-content { margin-left: 0; }
            .sidebar-overlay { display: none; position: fixed; top: 60px; left: 0; width: 100%; height: calc(100vh - 60px); background: rgba(0,0,0,0.5); z-index: 998; }
            .sidebar-overlay.show { display: block; }
        }
        @media (max-width: 576px) {
            .main-content { padding: 15px; }
            .page-title { font-size: 22px; }
        }
    </style>
    @stack('styles')
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
    <div class="container-fluid">
        <button class="btn btn-link text-white d-lg-none p-0 me-3" id="sidebarToggle">
            <i class="fas fa-bars fs-5"></i>
        </button>
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            <i class="fas fa-hospital-user"></i>
            <span class="d-none d-md-inline">SNGP</span>
        </a>
        <div class="d-flex align-items-center ms-auto">

            <!-- Notifications -->
            <div class="dropdown me-2">
                <button class="btn notifications-btn" data-bs-toggle="dropdown">
                    <i class="fas fa-bell"></i>
                    @php
                        $pharmacieId = Auth::user()->pharmacie_id;
                        $totalAlertes = $pharmacieId
                            ? \App\Models\Lot::where('pharmacie_id', $pharmacieId)
                                ->where(function($q) {
                                    $q->where('date_expiration', '<', now())
                                      ->orWhereBetween('date_expiration', [now(), now()->addDays(30)]);
                                })
                                ->where('quantite_disponible', '>', 0)->count()
                            : 0;
                    @endphp
                    @if($totalAlertes > 0)
                    <span class="notifications-badge">{{ $totalAlertes }}</span>
                    @endif
                </button>
                <ul class="dropdown-menu dropdown-menu-end" style="min-width: 300px;">
                    <li class="px-3 py-2"><strong>Notifications</strong></li>
                    <li><hr class="dropdown-divider m-0"></li>
                    @if($totalAlertes > 0)
                    <li>
                        <a class="dropdown-item py-2"
                            href="{{ Auth::user()->pharmacie_id ? route('lots.index') : '#' }}">
                            <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                            <span>{{ $totalAlertes }} alerte(s) de stock</span>
                        </a>
                    </li>
                    @else
                    <li><span class="dropdown-item text-muted py-2">
                        <i class="fas fa-check-circle text-success me-2"></i>Aucune notification
                    </span></li>
                    @endif
                    <li><hr class="dropdown-divider m-0"></li>
                    <li>
                        @if(Auth::user()->pharmacie_id)
                        <a class="dropdown-item text-center text-primary py-2" href="{{ route('lots.index') }}">
                            Voir toutes les alertes
                        </a>
                        @else
                        <a class="dropdown-item text-center text-muted py-2" href="#">
                            Voir toutes les alertes
                        </a>
                        @endif
                    </li>
                </ul>
            </div>

            <!-- User Dropdown -->
            <div class="dropdown user-dropdown">
                <button class="btn dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle me-2"></i>
                    <span class="d-none d-md-inline">{{ Auth::user()->prenom }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li class="px-3 py-2">
                        <strong>{{ Auth::user()->prenom }} {{ Auth::user()->nom }}</strong>
                        <br><small class="text-muted">{{ Auth::user()->email }}</small>
                        <br><small class="text-muted">{{ Auth::user()->getRoleNames()->first() }}</small>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('password.modifier.form') }}">
                            <i class="fas fa-key me-2"></i>Modifier mon mot de passe
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                            </button>
                        </form>
                    </li>
                </ul>
            </div>

        </div>
    </div>
</nav>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="user-profile">
            <div class="user-avatar">
                {{ strtoupper(substr(Auth::user()->prenom, 0, 1)) }}{{ strtoupper(substr(Auth::user()->nom, 0, 1)) }}
            </div>
            <div class="user-info">
                <h6>{{ Auth::user()->prenom }} {{ Auth::user()->nom }}</h6>
                <small>{{ Auth::user()->getRoleNames()->first() ?? 'Utilisateur' }}</small>
            </div>
        </div>
    </div>

    <div class="sidebar-menu">

        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}" class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt"></i><span>Tableau de bord</span>
        </a>

        {{-- ===== ADMIN NATIONAL ===== --}}
        @role('admin_national')
        <div class="menu-section">Administration</div>
        <a href="{{ route('pharmacies.index') }}" class="menu-item {{ request()->routeIs('pharmacies.*') ? 'active' : '' }}">
            <i class="fas fa-hospital"></i><span>Pharmacies</span>
        </a>
        <a href="{{ route('utilisateurs.index') }}" class="menu-item {{ request()->routeIs('utilisateurs.*') ? 'active' : '' }}">
            <i class="fas fa-users"></i><span>Utilisateurs</span>
        </a>
        <a href="{{ route('fournisseurs.index') }}" class="menu-item {{ request()->routeIs('fournisseurs.*') ? 'active' : '' }}">
            <i class="fas fa-truck"></i><span>Fournisseurs</span>
        </a>

        <div class="menu-section">Catalogue</div>
        <a href="{{ route('produits.index') }}" class="menu-item {{ request()->routeIs('produits.*') ? 'active' : '' }}">
            <i class="fas fa-pills"></i><span>Produits</span>
        </a>
        <a href="{{ route('categories.index') }}" class="menu-item {{ request()->routeIs('categories.*') ? 'active' : '' }}">
            <i class="fas fa-tags"></i><span>Catégories</span>
        </a>
        <a href="{{ route('maladies.index') }}" class="menu-item {{ request()->routeIs('maladies.*') ? 'active' : '' }}">
            <i class="fas fa-virus"></i><span>Maladies</span>
        </a>

        <div class="menu-section">Analyses</div>
        <a href="{{ route('statistiques.index') }}" class="menu-item {{ request()->routeIs('statistiques.*') ? 'active' : '' }}">
            <i class="fas fa-chart-line"></i><span>Statistiques</span>
        </a>
        <a href="{{ route('epidemiologie.index') }}" class="menu-item {{ request()->routeIs('epidemiologie.*') ? 'active' : '' }}">
            <i class="fas fa-stethoscope"></i><span>Suivi Épidémiologique</span>
        </a>

        <div class="menu-section">Système</div>
        <a href="{{ route('audit.index') }}" class="menu-item {{ request()->routeIs('audit.*') ? 'active' : '' }}">
            <i class="fas fa-history"></i><span>Journal d'audit</span>
        </a>
        <a href="{{ route('rapports.index') }}" class="menu-item {{ request()->routeIs('rapports.*') ? 'active' : '' }}">
            <i class="fas fa-file-alt"></i><span>Rapports</span>
        </a>
        @endrole

        {{-- ===== ADMIN PHARMACIE ===== --}}
        @role('admin_pharmacie')
        <div class="menu-section">Gestion</div>
        <a href="{{ route('produits.index') }}" class="menu-item {{ request()->routeIs('produits.*') ? 'active' : '' }}">
            <i class="fas fa-pills"></i><span>Produits</span>
        </a>
        @php
            $lotsAlerte = \App\Models\Lot::where('pharmacie_id', Auth::user()->pharmacie_id)
                ->where('date_expiration', '<', now()->addDays(30))
                ->where('quantite_disponible', '>', 0)->count();
        @endphp
        <a href="{{ route('lots.index') }}" class="menu-item {{ request()->routeIs('lots.*') ? 'active' : '' }}">
            <i class="fas fa-boxes"></i><span>Stocks & Lots</span>
            @if($lotsAlerte > 0)<span class="menu-badge">{{ $lotsAlerte }}</span>@endif
        </a>
        <a href="{{ route('ventes.index') }}" class="menu-item {{ request()->routeIs('ventes.*') ? 'active' : '' }}">
            <i class="fas fa-shopping-cart"></i><span>Ventes</span>
        </a>
        <a href="{{ route('commandes.index') }}" class="menu-item {{ request()->routeIs('commandes.*') ? 'active' : '' }}">
            <i class="fas fa-file-invoice"></i><span>Commandes</span>
        </a>
        <a href="{{ route('retours.index') }}" class="menu-item {{ request()->routeIs('retours.*') ? 'active' : '' }}">
            <i class="fas fa-undo"></i><span>Retours</span>
        </a>
        <a href="{{ route('utilisateurs.index') }}" class="menu-item {{ request()->routeIs('utilisateurs.*') ? 'active' : '' }}">
            <i class="fas fa-users"></i><span>Employés</span>
        </a>

        <div class="menu-section">Analyses</div>
        <a href="{{ route('statistiques.index') }}" class="menu-item {{ request()->routeIs('statistiques.*') ? 'active' : '' }}">
            <i class="fas fa-chart-bar"></i><span>Statistiques</span>
        </a>
        <a href="{{ route('epidemiologie.index') }}" class="menu-item {{ request()->routeIs('epidemiologie.*') ? 'active' : '' }}">
            <i class="fas fa-stethoscope"></i><span>Suivi Épidémiologique</span>
        </a>
        <a href="{{ route('audit.index') }}" class="menu-item {{ request()->routeIs('audit.*') ? 'active' : '' }}">
            <i class="fas fa-history"></i><span>Journal d'audit</span>
        </a>
        <a href="{{ route('rapports.index') }}" class="menu-item {{ request()->routeIs('rapports.*') ? 'active' : '' }}">
            <i class="fas fa-file-alt"></i><span>Rapports</span>
        </a>
        @endrole

        {{-- ===== PHARMACIEN ===== --}}
        @role('pharmacien')
        <div class="menu-section">Gestion</div>
        <a href="{{ route('produits.index') }}" class="menu-item {{ request()->routeIs('produits.*') ? 'active' : '' }}">
            <i class="fas fa-pills"></i><span>Produits</span>
        </a>
        <a href="{{ route('lots.index') }}" class="menu-item {{ request()->routeIs('lots.*') ? 'active' : '' }}">
            <i class="fas fa-boxes"></i><span>Stocks & Lots</span>
        </a>
        <a href="{{ route('ventes.index') }}" class="menu-item {{ request()->routeIs('ventes.*') ? 'active' : '' }}">
            <i class="fas fa-shopping-cart"></i><span>Ventes</span>
        </a>
        <a href="{{ route('retours.index') }}" class="menu-item {{ request()->routeIs('retours.*') ? 'active' : '' }}">
            <i class="fas fa-undo"></i><span>Retours</span>
        </a>
        <a href="{{ route('commandes.index') }}" class="menu-item {{ request()->routeIs('commandes.*') ? 'active' : '' }}">
            <i class="fas fa-file-invoice"></i><span>Commandes</span>
        </a>
        @endrole

        {{-- ===== CAISSIER ===== --}}
        @role('caissier')
        <div class="menu-section">Gestion</div>
        <a href="{{ route('produits.index') }}" class="menu-item {{ request()->routeIs('produits.*') ? 'active' : '' }}">
            <i class="fas fa-pills"></i><span>Produits</span>
        </a>
        <a href="{{ route('ventes.index') }}" class="menu-item {{ request()->routeIs('ventes.*') ? 'active' : '' }}">
            <i class="fas fa-shopping-cart"></i><span>Ventes</span>
        </a>
        <a href="{{ route('retours.index') }}" class="menu-item {{ request()->routeIs('retours.*') ? 'active' : '' }}">
            <i class="fas fa-undo"></i><span>Retours</span>
        </a>
        @endrole

        {{-- ===== GESTIONNAIRE STOCK ===== --}}
        @role('gestionnaire_stock')
        <div class="menu-section">Gestion</div>
        <a href="{{ route('produits.index') }}" class="menu-item {{ request()->routeIs('produits.*') ? 'active' : '' }}">
            <i class="fas fa-pills"></i><span>Produits</span>
        </a>
        <a href="{{ route('lots.index') }}" class="menu-item {{ request()->routeIs('lots.*') ? 'active' : '' }}">
            <i class="fas fa-boxes"></i><span>Stocks & Lots</span>
        </a>
        <a href="{{ route('commandes.index') }}" class="menu-item {{ request()->routeIs('commandes.*') ? 'active' : '' }}">
            <i class="fas fa-file-invoice"></i><span>Commandes</span>
        </a>
        @endrole

        {{-- ===== ASSISTANT PHARMACIEN ===== --}}
        @role('assistant_pharmacien')
        <div class="menu-section">Gestion</div>
        <a href="{{ route('produits.index') }}" class="menu-item {{ request()->routeIs('produits.*') ? 'active' : '' }}">
            <i class="fas fa-pills"></i><span>Produits</span>
        </a>
        <a href="{{ route('ventes.index') }}" class="menu-item {{ request()->routeIs('ventes.*') ? 'active' : '' }}">
            <i class="fas fa-shopping-cart"></i><span>Ventes</span>
        </a>
        <a href="{{ route('retours.index') }}" class="menu-item {{ request()->routeIs('retours.*') ? 'active' : '' }}">
            <i class="fas fa-undo"></i><span>Retours</span>
        </a>
        @endrole

        {{-- ===== FOURNISSEUR ===== --}}
        @role('fournisseur')
        <div class="menu-section">Espace Fournisseur</div>
        <a href="{{ route('fournisseur.espace.dashboard') }}" class="menu-item {{ request()->routeIs('fournisseur.espace.dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt"></i><span>Tableau de bord</span>
        </a>
        <a href="{{ route('fournisseur.espace.commandes') }}" class="menu-item {{ request()->routeIs('fournisseur.espace.commandes') || request()->routeIs('fournisseur.espace.commande.*') ? 'active' : '' }}">
            <i class="fas fa-file-invoice"></i><span>Mes Commandes</span>
            @php
                $nbNouvelles = \App\Models\Fournisseur::where('email', auth()->user()->email)->first()?->commandes()->where('statut','envoyee')->count() ?? 0;
            @endphp
            @if($nbNouvelles > 0)<span class="menu-badge">{{ $nbNouvelles }}</span>@endif
        </a>
        @endrole

    </div>
</div>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- MAIN CONTENT -->
<div class="main-content">

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <i class="fas fa-check-circle me-2"></i><strong>Succès !</strong> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4">
        <i class="fas fa-exclamation-circle me-2"></i><strong>Erreur !</strong> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show mb-4">
        <i class="fas fa-exclamation-triangle me-2"></i><strong>Attention !</strong> {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('info'))
    <div class="alert alert-info alert-dismissible fade show mb-4">
        <i class="fas fa-info-circle me-2"></i><strong>Information :</strong> {{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
        });
    }
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });
    }
    setTimeout(function() {
        document.querySelectorAll('.alert-dismissible').forEach(function(alert) {
            new bootstrap.Alert(alert).close();
        });
    }, 5000);
</script>
@stack('scripts')
</body>
</html>