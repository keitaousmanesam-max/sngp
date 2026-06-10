@extends('layouts.app')

@section('title', 'Journal d\'Audit')

@push('styles')
<style>
    .stats-card { background: white; border-radius: 12px; padding: 20px 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; border-left: 4px solid transparent; transition: all 0.3s; }
    .stats-card.total { border-left-color: #3B82F6; }
    .stats-card.jour { border-left-color: #10B981; }
    .stats-card.semaine { border-left-color: #F59E0B; }
    .stats-card.mois { border-left-color: #8B5CF6; }
    .stats-value { font-size: 28px; font-weight: 700; line-height: 1; }
    .stats-label { font-size: 12px; color: #6B7280; margin-top: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
    .filter-card { background: white; border-radius: 12px; padding: 20px 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; margin-bottom: 24px; }
    .table-card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; overflow: hidden; }
    .table-card .table { margin-bottom: 0; }
    .table-card thead { background: #F9FAFB; }
    .table-card thead th { border: none; color: #6B7280; font-weight: 600; font-size: 12px; text-transform: uppercase; padding: 14px 20px; }
    .table-card tbody td { padding: 12px 20px; vertical-align: middle; border-color: #F3F4F6; font-size: 14px; }
    .table-card tbody tr:hover { background: #F9FAFB; }
    .action-badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
    .action-badge.connexion { background: #D1FAE5; color: #065F46; }
    .action-badge.deconnexion { background: #F3F4F6; color: #374151; }
    .action-badge.creation { background: #DBEAFE; color: #1E40AF; }
    .action-badge.modification { background: #FEF3C7; color: #92400E; }
    .action-badge.suppression { background: #FEE2E2; color: #991B1B; }
    .action-badge.vente { background: #D1FAE5; color: #065F46; }
    .action-badge.retour { background: #FEF3C7; color: #92400E; }
    .action-badge.commande { background: #EDE9FE; color: #5B21B6; }
    .action-badge.default { background: #F3F4F6; color: #374151; }
</style>
@endpush

@section('content')

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-history me-2"></i>Journal d'Audit</h1>
        <p class="text-muted mb-0">Traçabilité complète des actions du système</p>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Statistiques -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stats-card total">
            <div class="stats-value" style="color: #3B82F6;">{{ $stats['total'] }}</div>
            <div class="stats-label"><i class="fas fa-history me-1"></i>Total Actions</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stats-card jour">
            <div class="stats-value" style="color: #10B981;">{{ $stats['aujourd_hui'] }}</div>
            <div class="stats-label"><i class="fas fa-calendar-day me-1"></i>Aujourd'hui</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stats-card semaine">
            <div class="stats-value" style="color: #F59E0B;">{{ $stats['cette_semaine'] }}</div>
            <div class="stats-label"><i class="fas fa-calendar-week me-1"></i>Cette Semaine</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stats-card mois">
            <div class="stats-value" style="color: #8B5CF6;">{{ $stats['ce_mois'] }}</div>
            <div class="stats-label"><i class="fas fa-calendar me-1"></i>Ce Mois</div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="filter-card">
    <form method="GET" action="{{ route('audit.index') }}">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label fw-semibold small">Rechercher</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control"
                        placeholder="Action, description, utilisateur..."
                        value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold small">Action</label>
                <select name="action" class="form-select">
                    <option value="">Toutes</option>
                    @foreach($actions as $action)
                    <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                        {{ ucfirst($action) }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold small">Date début</label>
                <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold small">Date fin</label>
                <input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}">
            </div>
            <div class="col-6 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="fas fa-filter me-1"></i>Filtrer
                </button>
                <a href="{{ route('audit.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Tableau -->
<div class="table-card">
    <div style="padding: 16px 24px; border-bottom: 1px solid #F3F4F6;">
        <h6 class="mb-0 fw-semibold" style="color: #374151;">
            {{ $audits->total() }} entrée(s)
        </h6>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Date & Heure</th>
                    <th>Utilisateur</th>
                    <th>Action</th>
                    <th>Module</th>
                    <th>Description</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($audits as $audit)
                @php
                    $actionClass = match($audit->action) {
                        'connexion'    => 'connexion',
                        'deconnexion'  => 'deconnexion',
                        'creation'     => 'creation',
                        'modification' => 'modification',
                        'suppression'  => 'suppression',
                        'vente'        => 'vente',
                        'retour'       => 'retour',
                        'commande'     => 'commande',
                        default        => 'default',
                    };
                    $actionIcon = match($audit->action) {
                        'connexion'    => 'fa-sign-in-alt',
                        'deconnexion'  => 'fa-sign-out-alt',
                        'creation'     => 'fa-plus-circle',
                        'modification' => 'fa-edit',
                        'suppression'  => 'fa-trash',
                        'vente'        => 'fa-shopping-cart',
                        'retour'       => 'fa-undo',
                        'commande'     => 'fa-file-invoice',
                        default        => 'fa-circle',
                    };
                @endphp
                <tr>
                    <td style="white-space: nowrap;">
                        <div class="fw-semibold" style="font-size: 13px;">
                            {{ $audit->created_at->format('d/m/Y') }}
                        </div>
                        <small class="text-muted">{{ $audit->created_at->format('H:i:s') }}</small>
                    </td>
                    <td>
                        @if($audit->user)
                        <div class="fw-semibold">
                            {{ $audit->user->prenom }} {{ $audit->user->nom }}
                        </div>
                        <small class="text-muted">{{ $audit->user->email }}</small>
                        @else
                        <span class="text-muted">Système</span>
                        @endif
                    </td>
                    <td>
                        <span class="action-badge {{ $actionClass }}">
                            <i class="fas {{ $actionIcon }} me-1"></i>
                            {{ ucfirst($audit->action) }}
                        </span>
                    </td>
                    <td>
                        @if($audit->module)
                        <span style="background: #EDE9FE; color: #5B21B6; padding: 3px 8px; border-radius: 6px; font-size: 12px; font-weight: 600;">
                            {{ ucfirst($audit->module) }}
                        </span>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td style="max-width: 300px;">
                        <div style="font-size: 13px; color: #374151;">
                            {{ $audit->description ?? '—' }}
                        </div>
                        @if($audit->model_type && $audit->model_id)
                        <small class="text-muted">
                            {{ class_basename($audit->model_type) }} #{{ $audit->model_id }}
                        </small>
                        @endif
                    </td>
                    <td>
                        <span style="font-family: monospace; font-size: 12px; color: #6B7280;">
                            {{ $audit->ip_address ?? '—' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div style="text-align: center; padding: 60px 20px; color: #9CA3AF;">
                            <i class="fas fa-history" style="font-size: 48px; opacity: 0.3; display: block; margin-bottom: 16px;"></i>
                            <p class="mb-0 fw-semibold">Aucune entrée dans le journal</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($audits->hasPages())
    <div style="padding: 16px 24px; border-top: 1px solid #F3F4F6;">
        {{ $audits->links() }}
    </div>
    @endif
</div>

@endsection