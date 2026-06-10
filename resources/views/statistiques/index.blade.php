@extends('layouts.app')

@section('title', 'Statistiques')

@push('styles')
<style>
    .stats-card { background: white; border-radius: 12px; padding: 20px 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; border-left: 4px solid transparent; }
    .stats-card.ventes { border-left-color: #10B981; }
    .stats-card.stocks { border-left-color: #3B82F6; }
    .stats-card.commandes { border-left-color: #8B5CF6; }
    .stats-card.retours { border-left-color: #F59E0B; }
    .stats-card.pharmacies { border-left-color: #EF4444; }
    .stats-value { font-size: 28px; font-weight: 700; line-height: 1; }
    .stats-label { font-size: 12px; color: #6B7280; margin-top: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
    .chart-card { background: white; border-radius: 16px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; margin-bottom: 24px; }
    .chart-title { font-size: 15px; font-weight: 600; color: #1F2937; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
    .filter-card { background: white; border-radius: 12px; padding: 20px 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; margin-bottom: 24px; }
    .period-btn { padding: 6px 14px; border-radius: 8px; border: 1px solid #E5E7EB; background: white; font-size: 13px; cursor: pointer; transition: all 0.2s; text-decoration: none; color: #374151; }
    .period-btn:hover, .period-btn.active { background: #1E3A8A; color: white; border-color: #1E3A8A; }
</style>
@endpush

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-chart-bar me-2"></i>Statistiques</h1>
        <p class="text-muted mb-0">Analyse des performances — {{ $dateDebut }} au {{ $dateFin }}</p>
    </div>
</div>

<!-- Filtres -->
<div class="filter-card">
    <form method="GET" action="{{ route('statistiques.index') }}">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-2">
                <label class="form-label fw-semibold small">Date début</label>
                <input type="date" name="date_debut" class="form-control" value="{{ $dateDebut }}">
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label fw-semibold small">Date fin</label>
                <input type="date" name="date_fin" class="form-control" value="{{ $dateFin }}">
            </div>
            <div class="col-12 col-md-5">
                <label class="form-label fw-semibold small">Période rapide</label>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('statistiques.index', ['date_debut' => now()->format('Y-m-d'), 'date_fin' => now()->format('Y-m-d')]) }}" class="period-btn">Aujourd'hui</a>
                    <a href="{{ route('statistiques.index', ['date_debut' => now()->startOfWeek()->format('Y-m-d'), 'date_fin' => now()->format('Y-m-d')]) }}" class="period-btn">Cette semaine</a>
                    <a href="{{ route('statistiques.index', ['date_debut' => now()->startOfMonth()->format('Y-m-d'), 'date_fin' => now()->format('Y-m-d')]) }}" class="period-btn">Ce mois</a>
                    <a href="{{ route('statistiques.index', ['date_debut' => now()->subDays(30)->format('Y-m-d'), 'date_fin' => now()->format('Y-m-d')]) }}" class="period-btn active">30 jours</a>
                    <a href="{{ route('statistiques.index', ['date_debut' => now()->startOfYear()->format('Y-m-d'), 'date_fin' => now()->format('Y-m-d')]) }}" class="period-btn">Cette année</a>
                </div>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2 align-items-end">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="fas fa-filter me-1"></i>Appliquer</button>
                <a href="{{ route('statistiques.index') }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
            </div>
        </div>
    </form>
</div>

<!-- KPIs Ventes -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stats-card ventes">
            <div class="stats-value" style="color: #10B981;">{{ $statsVentes['total'] }}</div>
            <div class="stats-label"><i class="fas fa-shopping-cart me-1"></i>Ventes</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stats-card ventes">
            <div class="stats-value" style="color: #065F46; font-size: 20px;">{{ number_format($statsVentes['ca_total'], 0, ',', ' ') }}</div>
            <div class="stats-label"><i class="fas fa-coins me-1"></i>CA Total (GNF)</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stats-card ventes">
            <div class="stats-value" style="color: #10B981; font-size: 20px;">{{ number_format($statsVentes['ca_moyen'], 0, ',', ' ') }}</div>
            <div class="stats-label"><i class="fas fa-chart-line me-1"></i>CA Moyen (GNF)</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stats-card ventes">
            <div class="stats-value" style="color: #10B981;">{{ $statsVentes['avec_ordonnance'] }}</div>
            <div class="stats-label"><i class="fas fa-prescription me-1"></i>Avec Ordonnance</div>
        </div>
    </div>
</div>

<!-- KPIs Stocks -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl">
        <div class="stats-card stocks">
            <div class="stats-value" style="color: #3B82F6;">{{ $statsStocks['total_lots'] }}</div>
            <div class="stats-label"><i class="fas fa-boxes me-1"></i>Total Lots</div>
        </div>
    </div>
    <div class="col-6 col-xl">
        <div class="stats-card stocks">
            <div class="stats-value" style="color: #10B981;">{{ $statsStocks['disponibles'] }}</div>
            <div class="stats-label"><i class="fas fa-check-circle me-1"></i>Disponibles</div>
        </div>
    </div>
    <div class="col-6 col-xl">
        <div class="stats-card stocks">
            <div class="stats-value" style="color: #EF4444;">{{ $statsStocks['expires'] }}</div>
            <div class="stats-label"><i class="fas fa-times-circle me-1"></i>Expirés</div>
        </div>
    </div>
    <div class="col-6 col-xl">
        <div class="stats-card stocks">
            <div class="stats-value" style="color: #F59E0B;">{{ $statsStocks['expiration_proche'] }}</div>
            <div class="stats-label"><i class="fas fa-clock me-1"></i>Expiration Proche</div>
        </div>
    </div>
    <div class="col-12 col-xl">
        <div class="stats-card stocks">
            <div class="stats-value" style="color: #3B82F6; font-size: 18px;">{{ number_format($statsStocks['valeur_stock'], 0, ',', ' ') }}</div>
            <div class="stats-label"><i class="fas fa-warehouse me-1"></i>Valeur Stock (GNF)</div>
        </div>
    </div>
</div>

<!-- KPIs Commandes & Retours -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stats-card commandes">
            <div class="stats-value" style="color: #8B5CF6;">{{ $statsCommandes['total'] }}</div>
            <div class="stats-label"><i class="fas fa-file-invoice me-1"></i>Commandes</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stats-card commandes">
            <div class="stats-value" style="color: #10B981;">{{ $statsCommandes['finalisees'] }}</div>
            <div class="stats-label"><i class="fas fa-check-circle me-1"></i>Finalisées</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stats-card retours">
            <div class="stats-value" style="color: #F59E0B;">{{ $statsRetours['total'] }}</div>
            <div class="stats-label"><i class="fas fa-undo me-1"></i>Retours</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stats-card retours">
            <div class="stats-value" style="color: #F59E0B; font-size: 20px;">{{ number_format($statsRetours['montant'], 0, ',', ' ') }}</div>
            <div class="stats-label"><i class="fas fa-coins me-1"></i>Montant Retours (GNF)</div>
        </div>
    </div>
</div>

@if($isNational && $statsPharmacies)
<div class="row g-3 mb-4">
    <div class="col-4">
        <div class="stats-card pharmacies">
            <div class="stats-value" style="color: #EF4444;">{{ $statsPharmacies['total'] }}</div>
            <div class="stats-label"><i class="fas fa-hospital me-1"></i>Total Pharmacies</div>
        </div>
    </div>
    <div class="col-4">
        <div class="stats-card pharmacies">
            <div class="stats-value" style="color: #10B981;">{{ $statsPharmacies['actives'] }}</div>
            <div class="stats-label"><i class="fas fa-check-circle me-1"></i>Actives</div>
        </div>
    </div>
    <div class="col-4">
        <div class="stats-card pharmacies">
            <div class="stats-value" style="color: #F59E0B;">{{ $statsPharmacies['suspendues'] }}</div>
            <div class="stats-label"><i class="fas fa-pause-circle me-1"></i>Suspendues</div>
        </div>
    </div>
</div>
@endif

<div class="row g-4">
    <!-- Graphique ventes par jour -->
    <div class="col-12 col-xl-8">
        <div class="chart-card">
            <div class="chart-title">
                <i class="fas fa-chart-area text-success"></i>
                Évolution des Ventes
            </div>
            <canvas id="chartVentes" height="120"></canvas>
        </div>
    </div>

    <!-- Top Produits graphique -->
    <div class="col-12 col-xl-4">
        <div class="chart-card">
            <div class="chart-title">
                <i class="fas fa-pills text-primary"></i>
                Top 5 Produits
            </div>
            <canvas id="chartProduits" height="200"></canvas>
        </div>
    </div>

    <!-- Tableau Top Produits -->
    <div class="col-12 col-xl-6">
        <div class="chart-card">
            <div class="chart-title">
                <i class="fas fa-trophy text-warning"></i>
                Top Produits Vendus
            </div>
            <div class="table-responsive">
                <table class="table" style="margin-bottom: 0;">
                    <thead style="background: #F9FAFB;">
                        <tr>
                            <th style="padding: 12px 16px; font-size: 12px; color: #6B7280; font-weight: 600; border: none;">#</th>
                            <th style="padding: 12px 16px; font-size: 12px; color: #6B7280; font-weight: 600; border: none;">Produit</th>
                            <th style="padding: 12px 16px; font-size: 12px; color: #6B7280; font-weight: 600; border: none;">Qté</th>
                            <th style="padding: 12px 16px; font-size: 12px; color: #6B7280; font-weight: 600; border: none;">CA (GNF)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topProduits as $i => $ligne)
                        <tr>
                            <td style="padding: 12px 16px; border-color: #F3F4F6;">
                                @if($i == 0) <span style="font-size: 18px;">🥇</span>
                                @elseif($i == 1) <span style="font-size: 18px;">🥈</span>
                                @elseif($i == 2) <span style="font-size: 18px;">🥉</span>
                                @else <span class="text-muted fw-semibold">{{ $i + 1 }}</span>
                                @endif
                            </td>
                            <td style="padding: 12px 16px; border-color: #F3F4F6;">
                                <div class="fw-semibold">{{ $ligne->produit->dci ?? '—' }}</div>
                                <small class="text-muted">{{ $ligne->produit->dosage ?? '' }}</small>
                            </td>
                            <td style="padding: 12px 16px; border-color: #F3F4F6; font-weight: 600; color: #3B82F6;">
                                {{ number_format($ligne->total_vendu) }}
                            </td>
                            <td style="padding: 12px 16px; border-color: #F3F4F6; font-weight: 600; color: #065F46;">
                                {{ number_format($ligne->ca_total, 0, ',', ' ') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Aucune vente sur la période</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($isNational && $topPharmacies)
    <div class="col-12 col-xl-6">
        <div class="chart-card">
            <div class="chart-title">
                <i class="fas fa-hospital text-danger"></i>
                Top Pharmacies par CA
            </div>
            <div class="table-responsive">
                <table class="table" style="margin-bottom: 0;">
                    <thead style="background: #F9FAFB;">
                        <tr>
                            <th style="padding: 12px 16px; font-size: 12px; color: #6B7280; font-weight: 600; border: none;">#</th>
                            <th style="padding: 12px 16px; font-size: 12px; color: #6B7280; font-weight: 600; border: none;">Pharmacie</th>
                            <th style="padding: 12px 16px; font-size: 12px; color: #6B7280; font-weight: 600; border: none;">Ventes</th>
                            <th style="padding: 12px 16px; font-size: 12px; color: #6B7280; font-weight: 600; border: none;">CA (GNF)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topPharmacies as $i => $ligne)
                        <tr>
                            <td style="padding: 12px 16px; border-color: #F3F4F6;">
                                @if($i == 0) <span style="font-size: 18px;">🥇</span>
                                @elseif($i == 1) <span style="font-size: 18px;">🥈</span>
                                @elseif($i == 2) <span style="font-size: 18px;">🥉</span>
                                @else <span class="text-muted fw-semibold">{{ $i + 1 }}</span>
                                @endif
                            </td>
                            <td style="padding: 12px 16px; border-color: #F3F4F6;" class="fw-semibold">{{ $ligne->pharmacie->nom ?? '—' }}</td>
                            <td style="padding: 12px 16px; border-color: #F3F4F6; color: #3B82F6;">{{ number_format($ligne->nb_ventes) }}</td>
                            <td style="padding: 12px 16px; border-color: #F3F4F6; font-weight: 600; color: #065F46;">{{ number_format($ligne->ca_total, 0, ',', ' ') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Aucune donnée</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ventesLabels = [
        @foreach($ventesParJour as $v)
            "{{ \Carbon\Carbon::parse($v->date)->format('d/m') }}",
        @endforeach
    ];
    const ventesCA = [
        @foreach($ventesParJour as $v)
            {{ $v->ca }},
        @endforeach
    ];
    const ventesNb = [
        @foreach($ventesParJour as $v)
            {{ $v->nb }},
        @endforeach
    ];

    new Chart(document.getElementById('chartVentes'), {
        type: 'line',
        data: {
            labels: ventesLabels,
            datasets: [{
                label: 'CA (GNF)',
                data: ventesCA,
                borderColor: '#10B981',
                backgroundColor: 'rgba(16,185,129,0.1)',
                fill: true,
                tension: 0.4,
                yAxisID: 'y',
            }, {
                label: 'Nb Ventes',
                data: ventesNb,
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59,130,246,0.1)',
                fill: false,
                tension: 0.4,
                yAxisID: 'y1',
            }]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'top' } },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    ticks: {
                        precision: 0,
                        callback: function(value) {
                            if (Number.isInteger(value)) {
                                return new Intl.NumberFormat('fr-FR').format(value) + ' GNF';
                            }
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    ticks: {
                        precision: 0,
                        callback: function(value) {
                            if (Number.isInteger(value)) return value;
                        }
                    }
                }
            }
        }
    });

    const topProduitsLabels = [
        @foreach($topProduits->take(5) as $p)
            "{{ \Illuminate\Support\Str::limit($p->produit->dci ?? 'Inconnu', 15) }}",
        @endforeach
    ];
    const topProduitsData = [
        @foreach($topProduits->take(5) as $p)
            {{ $p->total_vendu }},
        @endforeach
    ];

    new Chart(document.getElementById('chartProduits'), {
        type: 'doughnut',
        data: {
            labels: topProduitsLabels,
            datasets: [{
                data: topProduitsData,
                backgroundColor: ['#10B981', '#3B82F6', '#8B5CF6', '#F59E0B', '#EF4444'],
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 11 } } }
            }
        }
    });
</script>
@endpush