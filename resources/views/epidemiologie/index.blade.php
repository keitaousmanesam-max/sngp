@extends('layouts.app')

@section('title', 'Suivi Épidémiologique')

@push('styles')
<style>
    .stats-card { background: white; border-radius: 12px; padding: 20px 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; border-left: 4px solid #1E3A8A; }
    .stats-value { font-size: 28px; font-weight: 700; color: #1E3A8A; }
    .stats-label { font-size: 12px; color: #6B7280; margin-top: 4px; text-transform: uppercase; }
    .chart-card { background: white; border-radius: 16px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; margin-bottom: 24px; }
    .chart-title { font-size: 15px; font-weight: 600; color: #1F2937; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
    .filter-card { background: white; border-radius: 12px; padding: 20px 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; margin-bottom: 24px; }
    .period-btn { padding: 6px 14px; border-radius: 8px; border: 1px solid #E5E7EB; background: white; font-size: 13px; cursor: pointer; transition: all 0.2s; text-decoration: none; color: #374151; }
    .period-btn:hover, .period-btn.active { background: #1E3A8A; color: white; border-color: #1E3A8A; }
    .maladie-bar { height: 8px; border-radius: 4px; background: linear-gradient(90deg, #3B82F6, #1E3A8A); }
    .rank-badge { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; }
    .region-card { background: #F9FAFB; border-radius: 10px; padding: 16px; margin-bottom: 12px; }
    .region-title { font-size: 13px; font-weight: 700; color: #1E3A8A; margin-bottom: 10px; }
</style>
@endpush

@section('content')

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-stethoscope me-2"></i>Suivi Épidémiologique</h1>
        <p class="text-muted mb-0">Analyse des maladies traitées via les ventes de médicaments</p>
    </div>
</div>

<!-- Filtres -->
<div class="filter-card">
    <form method="GET" action="{{ route('epidemiologie.index') }}">
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
                    <a href="{{ route('epidemiologie.index', ['date_debut' => now()->startOfWeek()->format('Y-m-d'), 'date_fin' => now()->format('Y-m-d')]) }}" class="period-btn">Cette semaine</a>
                    <a href="{{ route('epidemiologie.index', ['date_debut' => now()->startOfMonth()->format('Y-m-d'), 'date_fin' => now()->format('Y-m-d')]) }}" class="period-btn">Ce mois</a>
                    <a href="{{ route('epidemiologie.index', ['date_debut' => now()->subDays(30)->format('Y-m-d'), 'date_fin' => now()->format('Y-m-d')]) }}" class="period-btn active">30 jours</a>
                    <a href="{{ route('epidemiologie.index', ['date_debut' => now()->startOfYear()->format('Y-m-d'), 'date_fin' => now()->format('Y-m-d')]) }}" class="period-btn">Cette année</a>
                </div>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2 align-items-end">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="fas fa-filter me-1"></i>Appliquer</button>
                <a href="{{ route('epidemiologie.index') }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
            </div>
        </div>
    </form>
</div>

<!-- KPIs -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stats-card">
            <div class="stats-value">{{ $stats['total_maladies'] }}</div>
            <div class="stats-label"><i class="fas fa-virus me-1"></i>Maladies enregistrées</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stats-card" style="border-left-color: #10B981;">
            <div class="stats-value" style="color: #10B981;">{{ $stats['maladies_actives'] }}</div>
            <div class="stats-label"><i class="fas fa-chart-line me-1"></i>Maladies détectées</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stats-card" style="border-left-color: #8B5CF6;">
            <div class="stats-value" style="color: #8B5CF6;">{{ number_format($stats['total_doses']) }}</div>
            <div class="stats-label"><i class="fas fa-pills me-1"></i>Doses dispensées</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stats-card" style="border-left-color: #F59E0B;">
            <div class="stats-value" style="color: #F59E0B;">{{ number_format($stats['total_ventes']) }}</div>
            <div class="stats-label"><i class="fas fa-shopping-cart me-1"></i>Ventes associées</div>
        </div>
    </div>
</div>

<div class="row g-4">

    <!-- Top Maladies -->
    <div class="col-12 col-xl-8">
        <div class="chart-card">
            <div class="chart-title">
                <i class="fas fa-trophy text-warning"></i>
                Top Maladies Traitées
                <small class="text-muted ms-auto">{{ $dateDebut }} → {{ $dateFin }}</small>
            </div>
            @if($topMaladies->count() > 0)
                @php $maxDoses = $topMaladies->first()->total_medicaments; @endphp
                @foreach($topMaladies as $i => $maladie)
                <div class="d-flex align-items-center mb-3">
                    <div class="rank-badge me-3 {{ $i == 0 ? 'bg-warning text-dark' : ($i == 1 ? 'bg-secondary text-white' : ($i == 2 ? 'bg-danger text-white' : 'bg-light text-muted')) }}">
                        {{ $i + 1 }}
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-semibold" style="font-size: 13px;">
                                {{ $maladie->nom }}
                                @if($maladie->code_cim10)
                                    <span class="badge bg-light text-secondary ms-1" style="font-size: 10px;">{{ $maladie->code_cim10 }}</span>
                                @endif
                            </span>
                            <span class="fw-bold" style="font-size: 13px; color: #1E3A8A;">
                                {{ number_format($maladie->total_medicaments) }} doses
                            </span>
                        </div>
                        <div style="background: #F3F4F6; border-radius: 4px; height: 8px;">
                            <div class="maladie-bar" style="width: {{ $maxDoses > 0 ? ($maladie->total_medicaments / $maxDoses * 100) : 0 }}%;"></div>
                        </div>
                        <small class="text-muted">{{ $maladie->nb_ventes }} ventes · {{ $maladie->nb_produits }} produit(s)
                            @if($maladie->categorie) · {{ $maladie->categorie }}@endif
                        </small>
                    </div>
                </div>
                @endforeach
            @else
                <div class="text-center text-muted py-5">
                    <i class="fas fa-chart-bar fa-3x mb-3" style="color: #D1D5DB;"></i>
                    <p>Aucune donnée sur la période sélectionnée.</p>
                    <small>Assurez-vous que les produits sont associés à des maladies.</small>
                </div>
            @endif
        </div>
    </div>

    <!-- Maladies par catégorie -->
    <div class="col-12 col-xl-4">
        <div class="chart-card">
            <div class="chart-title">
                <i class="fas fa-tags text-primary"></i>
                Par Catégorie
            </div>
            <canvas id="chartCategories" height="250"></canvas>
        </div>
    </div>

    <!-- Évolution hebdomadaire -->
    <div class="col-12">
        <div class="chart-card">
            <div class="chart-title">
                <i class="fas fa-chart-area text-success"></i>
                Évolution Hebdomadaire des Doses Dispensées
            </div>
            <canvas id="chartEvolution" height="80"></canvas>
        </div>
    </div>

    @if($isNational && $maladiesParRegion && $maladiesParRegion->count() > 0)
    <!-- Maladies par région -->
    <div class="col-12">
        <div class="chart-card">
            <div class="chart-title">
                <i class="fas fa-map-marker-alt text-danger"></i>
                Maladies par Région
            </div>
            <div class="row g-3">
                @foreach($maladiesParRegion as $region => $maladies)
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="region-card">
                        <div class="region-title"><i class="fas fa-map-marker-alt me-1"></i>{{ $region }}</div>
                        @foreach($maladies->take(5) as $i => $m)
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span style="font-size: 12px;">{{ $i + 1 }}. {{ $m->maladie }}</span>
                            <span class="badge" style="background: #EFF6FF; color: #1E3A8A; font-size: 11px;">{{ number_format($m->total) }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Graphique évolution hebdomadaire
    const evolutionLabels = [
        @foreach($evolutionHebdo as $e)
            "{{ \Carbon\Carbon::parse($e->debut_semaine)->format('d/m') }}",
        @endforeach
    ];
    const evolutionData = [
        @foreach($evolutionHebdo as $e)
            {{ $e->total }},
        @endforeach
    ];

    new Chart(document.getElementById('chartEvolution'), {
        type: 'bar',
        data: {
            labels: evolutionLabels,
            datasets: [{
                label: 'Doses dispensées',
                data: evolutionData,
                backgroundColor: 'rgba(59,130,246,0.7)',
                borderColor: '#1E3A8A',
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        callback: function(v) {
                            if (Number.isInteger(v)) return new Intl.NumberFormat('fr-FR').format(v);
                        }
                    }
                }
            }
        }
    });

    // Graphique catégories
    const catLabels = [
        @foreach($maladiesParCategorie as $c)
            "{{ $c->categorie }}",
        @endforeach
    ];
    const catData = [
        @foreach($maladiesParCategorie as $c)
            {{ $c->total }},
        @endforeach
    ];
    const catColors = ['#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6','#06B6D4','#84CC16','#F97316'];

    new Chart(document.getElementById('chartCategories'), {
        type: 'doughnut',
        data: {
            labels: catLabels,
            datasets: [{
                data: catData,
                backgroundColor: catColors.slice(0, catLabels.length),
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