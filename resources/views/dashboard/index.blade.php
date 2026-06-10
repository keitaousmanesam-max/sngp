@extends('layouts.app')

@section('title', 'Tableau de bord')

@push('styles')
<style>
    .kpi-card-modern {
        background: white;
        border-radius: 16px;
        padding: 28px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border: 1px solid #f0f0f0;
        transition: all 0.3s ease;
        height: 100%;
        position: relative;
        overflow: hidden;
    }
    .kpi-card-modern::before {
        content: '';
        position: absolute;
        top: 0; left: 0;
        width: 4px; height: 100%;
        background: linear-gradient(180deg, var(--primary-color), var(--secondary-color));
    }
    .kpi-card-modern:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.12); }
    .kpi-value-large { font-size: 36px; font-weight: 700; line-height: 1; margin: 12px 0 8px 0; }
    .kpi-label-modern { font-size: 13px; color: #6B7280; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }
    .kpi-trend { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; margin-top: 8px; }
    .kpi-trend.up { background: #DEF7EC; color: #03543F; }
    .kpi-trend.down { background: #FDE8E8; color: #9B1C1C; }
    .kpi-icon-modern { width: 64px; height: 64px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 28px; color: white; position: absolute; right: 24px; top: 50%; transform: translateY(-50%); opacity: 0.15; }
    .chart-card-modern { background: white; border-radius: 16px; padding: 28px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border: 1px solid #f0f0f0; height: 100%; }
    .chart-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid #F3F4F6; }
    .chart-title { font-size: 18px; font-weight: 600; color: #1F2937; display: flex; align-items: center; }
    .alert-card-modern { background: white; border-radius: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border: 1px solid #f0f0f0; height: 100%; overflow: hidden; }
    .alert-item { padding: 18px 24px; border-bottom: 1px solid #F3F4F6; transition: background 0.2s; }
    .alert-item:last-child { border-bottom: none; }
    .alert-item:hover { background: #F9FAFB; }
    .alert-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
    .alert-icon.warning { background: #FEF3C7; color: #D97706; }
    .alert-icon.danger { background: #FEE2E2; color: #DC2626; }
    .alert-icon.info { background: #DBEAFE; color: #2563EB; }
    .alert-icon.success { background: #D1FAE5; color: #059669; }
    .activity-table { background: white; border-radius: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border: 1px solid #f0f0f0; overflow: hidden; }
    .activity-table .table { margin-bottom: 0; }
    .activity-table thead { background: #F9FAFB; }
    .activity-table thead th { border: none; color: #6B7280; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; padding: 16px 20px; }
    .activity-table tbody td { padding: 16px 20px; vertical-align: middle; border-color: #F3F4F6; }
    .activity-table tbody tr:hover { background: #F9FAFB; }
    .badge-modern { padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; }
    .badge-modern.type-vente { background: #D1FAE5; color: #065F46; }
    .badge-modern.type-stock { background: #DBEAFE; color: #1E40AF; }
    .badge-modern.type-commande { background: #FEF3C7; color: #92400E; }
    .badge-modern.type-auth { background: #EDE9FE; color: #5B21B6; }
    .badge-modern.type-default { background: #F3F4F6; color: #374151; }
    .period-btn-group { display: flex; gap: 8px; }
    .period-btn { padding: 8px 16px; border: 2px solid #E5E7EB; background: white; border-radius: 8px; font-size: 13px; font-weight: 600; color: #6B7280; cursor: pointer; transition: all 0.2s; }
    .period-btn:hover { border-color: var(--secondary-color); color: var(--secondary-color); }
    .period-btn.active { background: var(--secondary-color); border-color: var(--secondary-color); color: white; }
    .empty-state { text-align: center; padding: 60px 20px; color: #9CA3AF; }
    .empty-state i { font-size: 64px; margin-bottom: 16px; opacity: 0.3; }
</style>
@endpush

@section('content')

<!-- Page Header -->
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1 class="page-title mb-2">
                <i class="fas fa-tachometer-alt me-2"></i>Tableau de bord
            </h1>
            <p class="page-subtitle mb-0">
                Vue d'ensemble du système national -
                <strong>{{ \Carbon\Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</strong>
            </p>
        </div>
        <div class="col-md-4 text-end">
            @role('admin_national')
            <a href="{{ route('dashboard') }}" class="btn btn-primary">
                <i class="fas fa-download me-2"></i>Exporter Rapport
            </a>
            @endrole
        </div>
    </div>
</div>

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- KPI Cards -->
<div class="row g-4 mb-4">

    @role('admin_national')
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card-modern">
            <div class="kpi-icon-modern" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));"><i class="fas fa-hospital"></i></div>
            <div class="kpi-label-modern">Pharmacies Actives</div>
            <div class="kpi-value-large" style="color: var(--primary-color);">{{ $stats['pharmacies_actives'] ?? 0 }}</div>
            <span class="kpi-trend up"><i class="fas fa-hospital me-1"></i>{{ $stats['pharmacies_actives'] ?? 0 }} / {{ $stats['total_pharmacies'] ?? 0 }} total</span>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card-modern">
            <div class="kpi-icon-modern" style="background: linear-gradient(135deg, #10B981, #059669);"><i class="fas fa-pills"></i></div>
            <div class="kpi-label-modern">Produits Référencés</div>
            <div class="kpi-value-large" style="color: #10B981;">{{ $stats['produits_references'] ?? 0 }}</div>
            <span class="kpi-trend up"><i class="fas fa-plus me-1"></i>Catalogue National</span>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card-modern">
            <div class="kpi-icon-modern" style="background: linear-gradient(135deg, #3B82F6, #2563EB);"><i class="fas fa-chart-line"></i></div>
            <div class="kpi-label-modern">Ventes Totales</div>
            <div class="kpi-value-large" style="color: #3B82F6;">{{ number_format($stats['total_ventes'] ?? 0) }}</div>
            <span class="kpi-trend up"><i class="fas fa-shopping-cart me-1"></i>{{ $stats['ventes_aujourd_hui'] ?? 0 }} aujourd'hui</span>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card-modern">
            <div class="kpi-icon-modern" style="background: linear-gradient(135deg, #EF4444, #DC2626);"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="kpi-label-modern">Lots Expirés</div>
            <div class="kpi-value-large" style="color: #EF4444;">{{ $stats['lots_expires'] ?? 0 }}</div>
            <span class="kpi-trend {{ ($stats['lots_expires'] ?? 0) > 0 ? 'down' : 'up' }}">
                <i class="fas fa-exclamation-circle me-1"></i>
                {{ ($stats['lots_expires'] ?? 0) > 0 ? 'Action Requise' : 'RAS' }}
            </span>
        </div>
    </div>
    @endrole

    @role('admin_pharmacie')
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card-modern">
            <div class="kpi-icon-modern" style="background: linear-gradient(135deg, #10B981, #059669);"><i class="fas fa-pills"></i></div>
            <div class="kpi-label-modern">Produits Disponibles</div>
            <div class="kpi-value-large" style="color: #10B981;">{{ $stats['produits_references'] ?? 0 }}</div>
            <span class="kpi-trend up"><i class="fas fa-check me-1"></i>En Catalogue</span>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card-modern">
            <div class="kpi-icon-modern" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));"><i class="fas fa-boxes"></i></div>
            <div class="kpi-label-modern">Lots en Stock</div>
            <div class="kpi-value-large" style="color: var(--primary-color);">{{ $stats['lots_disponibles'] ?? 0 }}</div>
            <span class="kpi-trend up"><i class="fas fa-warehouse me-1"></i>Disponibles</span>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card-modern">
            <div class="kpi-icon-modern" style="background: linear-gradient(135deg, #3B82F6, #2563EB);"><i class="fas fa-shopping-cart"></i></div>
            <div class="kpi-label-modern">Ventes ce Mois</div>
            <div class="kpi-value-large" style="color: #3B82F6;">{{ $stats['ventes_mois'] ?? 0 }}</div>
            <span class="kpi-trend up"><i class="fas fa-calendar me-1"></i>{{ \Carbon\Carbon::now()->locale('fr')->isoFormat('MMMM') }}</span>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card-modern">
            <div class="kpi-icon-modern" style="background: linear-gradient(135deg, #EF4444, #DC2626);"><i class="fas fa-exclamation-circle"></i></div>
            <div class="kpi-label-modern">Lots Expirés</div>
            <div class="kpi-value-large" style="color: #EF4444;">{{ $stats['lots_expires'] ?? 0 }}</div>
            <span class="kpi-trend {{ ($stats['lots_expires'] ?? 0) > 0 ? 'down' : 'up' }}">
                <i class="fas fa-trash me-1"></i>À Retirer
            </span>
        </div>
    </div>
    @endrole

</div>

<!-- Graphique et Alertes -->
<div class="row g-4 mb-4">

    <!-- Graphique des Ventes -->
    <div class="col-12 col-xl-8">
        <div class="chart-card-modern">
            <div class="chart-header">
                <h5 class="chart-title">
                    <i class="fas fa-chart-area me-2"></i>Évolution des Ventes
                </h5>
                <div class="period-btn-group">
                    <button class="period-btn active">30J</button>
                    <button class="period-btn">90J</button>
                    <button class="period-btn">1 An</button>
                </div>
            </div>
            <canvas id="ventesChart" height="100"></canvas>
        </div>
    </div>

    <!-- Alertes Récentes -->
    <div class="col-12 col-xl-4">
        <div class="alert-card-modern">
            <div style="padding: 24px 24px 16px; border-bottom: 2px solid #F3F4F6;">
                <h5 class="mb-0" style="font-size: 18px; font-weight: 600; color: #1F2937;">
                    <i class="fas fa-bell me-2" style="color: #D97706;"></i>Alertes Récentes
                </h5>
            </div>
            <div style="max-height: 380px; overflow-y: auto;">
                @forelse($alertes as $alerte)
                <div class="alert-item">
                    <div class="d-flex align-items-start">
                        <div class="alert-icon {{ $alerte['type'] }} me-3">
                            <i class="fas {{ $alerte['icon'] }}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <strong style="font-size: 14px; color: #1F2937;">{{ $alerte['titre'] }}</strong>
                                <small class="text-muted ms-2">{{ \Carbon\Carbon::parse($alerte['date'])->diffForHumans() }}</small>
                            </div>
                            <p class="mb-0" style="font-size: 13px; color: #6B7280;">{{ $alerte['message'] }}</p>
                        </div>
                    </div>
                </div>
                @empty
                <div style="text-align: center; padding: 50px 20px;">
                    <i class="fas fa-check-circle" style="font-size: 48px; color: #10B981; opacity: 0.4;"></i>
                    <p class="mb-0 mt-3" style="color: #6B7280;"><strong>Aucune alerte</strong><br><small>Tout est en ordre !</small></p>
                </div>
                @endforelse
            </div>
            @if($alertes->count() > 0)
            <div style="padding: 16px 24px; border-top: 1px solid #F3F4F6; text-align: center;">
                <a href="#" class="text-decoration-none fw-bold" style="color: var(--secondary-color); font-size: 14px;">
                    Voir toutes les alertes ({{ $alertes->count() }}) <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
            @endif
        </div>
    </div>

</div>

<!-- Activités Récentes -->
<div class="row g-4">
    <div class="col-12">
        <div class="activity-table">
            <div style="padding: 24px 24px 16px; border-bottom: 2px solid #F3F4F6;">
                <h5 class="mb-0" style="font-size: 18px; font-weight: 600; color: #1F2937;">
                    <i class="fas fa-history me-2" style="color: var(--secondary-color);"></i>Activités Récentes
                </h5>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 150px;">Type</th>
                            <th>Description</th>
                            <th style="width: 200px;">Utilisateur</th>
                            <th style="width: 180px;">Date</th>
                            <th style="width: 120px;">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activites as $activite)
                        <tr>
                            <td>
                                @php
                                    $module = strtolower($activite->module ?? '');
                                    $typeClass = match(true) {
                                        str_contains($module, 'vente')    => 'type-vente',
                                        str_contains($module, 'stock')    => 'type-stock',
                                        str_contains($module, 'commande') => 'type-commande',
                                        str_contains($module, 'auth')     => 'type-auth',
                                        default                           => 'type-default'
                                    };
                                    $typeIcon = match(true) {
                                        str_contains($module, 'vente')    => 'fa-shopping-cart',
                                        str_contains($module, 'stock')    => 'fa-box',
                                        str_contains($module, 'commande') => 'fa-file-invoice',
                                        str_contains($module, 'auth')     => 'fa-user',
                                        default                           => 'fa-circle'
                                    };
                                @endphp
                                <span class="badge-modern {{ $typeClass }}">
                                    <i class="fas {{ $typeIcon }} me-1"></i>
                                    {{ ucfirst($activite->module ?? 'Système') }}
                                </span>
                            </td>
                            <td style="font-size: 14px; color: #374151;">
                                {{ $activite->description ?? $activite->action }}
                            </td>
                            <td style="font-size: 14px; color: #6B7280;">
                                @if($activite->user)
                                    <i class="fas fa-user-circle me-1"></i>
                                    {{ $activite->user->prenom }} {{ $activite->user->nom }}
                                @else
                                    <span class="text-muted">Système</span>
                                @endif
                            </td>
                            <td style="font-size: 13px; color: #9CA3AF;">
                                {{ $activite->created_at->diffForHumans() }}
                            </td>
                            <td>
                                @php
                                    $action = strtolower($activite->action ?? '');
                                    $statusClass = match(true) {
                                        str_contains($action, 'login')   => 'status-valide',
                                        str_contains($action, 'create')  => 'status-complete',
                                        str_contains($action, 'delete')  => 'status-attente',
                                        default                          => 'status-complete'
                                    };
                                    $statusLabel = match(true) {
                                        str_contains($action, 'login')   => 'Connexion',
                                        str_contains($action, 'logout')  => 'Déconnexion',
                                        str_contains($action, 'create')  => 'Créé',
                                        str_contains($action, 'update')  => 'Modifié',
                                        str_contains($action, 'delete')  => 'Supprimé',
                                        default                          => 'Complété'
                                    };
                                @endphp
                                <span class="badge-modern {{ $statusClass }}">{{ $statusLabel }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <p class="mb-0">Aucune activité récente</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('ventesChart');
    const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(59, 130, 246, 0.2)');
    gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');

    const ventesLabels = @json($ventesParJour['labels'] ?? []);
    const ventesData   = @json($ventesParJour['data'] ?? []);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ventesLabels,
            datasets: [{
                label: 'Ventes',
                data: ventesData,
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: gradient,
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointBackgroundColor: 'white',
                pointBorderColor: 'rgb(59, 130, 246)',
                pointBorderWidth: 2,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'white',
                    titleColor: '#1F2937',
                    bodyColor: '#6B7280',
                    borderColor: '#E5E7EB',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: false,
                    callbacks: {
                        label: ctx => ctx.parsed.y + ' transaction(s)'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#F3F4F6', drawBorder: false },
                    ticks: {
                        color: '#9CA3AF',
                        font: { size: 12 },
                        precision: 0,
                        callback: function(value) {
                            if (Number.isInteger(value)) return value;
                        }
                    }
                },
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: { color: '#9CA3AF', font: { size: 11 }, maxTicksLimit: 10 }
                }
            }
        }
    });

    document.querySelectorAll('.period-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });
</script>
@endpush