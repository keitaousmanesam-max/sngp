@extends('layouts.app')

@section('title', 'Mes Commandes')

@push('styles')
<style>
    .page-header { background: linear-gradient(135deg, #1E3A8A, #3B82F6); border-radius: 16px; padding: 28px 32px; color: white; margin-bottom: 28px; display: flex; align-items: center; justify-content: space-between; flex-wrap: gap; }
    .filter-card { background: white; border-radius: 14px; padding: 20px 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; margin-bottom: 24px; }
    .table-card { background: white; border-radius: 14px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; overflow: hidden; }
    .table-card table { margin-bottom: 0; }
    .table-card thead { background: #F8FAFF; }
    .table-card thead th { border: none; color: #6B7280; font-weight: 700; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; padding: 14px 20px; }
    .table-card tbody td { padding: 14px 20px; vertical-align: middle; border-color: #F3F4F6; font-size: 14px; }
    .table-card tbody tr:hover { background: #F8FAFF; }

    .badge-statut { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .badge-statut.en_attente    { background: #F3F4F6; color: #374151; }
    .badge-statut.envoyee       { background: #DBEAFE; color: #1E40AF; }
    .badge-statut.en_traitement { background: #EDE9FE; color: #5B21B6; }
    .badge-statut.expediee      { background: #FEF3C7; color: #92400E; }
    .badge-statut.finalisee     { background: #D1FAE5; color: #065F46; }
    .badge-statut.annulee       { background: #FEE2E2; color: #991B1B; }

    .statut-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 5px; }
    .dot-en_attente    { background: #9CA3AF; }
    .dot-envoyee       { background: #3B82F6; }
    .dot-en_traitement { background: #8B5CF6; }
    .dot-expediee      { background: #F59E0B; }
    .dot-finalisee     { background: #10B981; }
    .dot-annulee       { background: #EF4444; }

    .btn-action { padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; border: none; cursor: pointer; white-space: nowrap; }
    .btn-action:hover { opacity: 0.85; }
    .btn-voir { background: #EFF6FF; color: #1E40AF; }
    .btn-confirmer { background: #1E3A8A; color: white; }
    .btn-expedier  { background: #7C3AED; color: white; }

    .pharmacie-avatar { width: 36px; height: 36px; border-radius: 9px; background: linear-gradient(135deg, #1E3A8A, #3B82F6); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 14px; flex-shrink: 0; }
</style>
@endpush

@section('content')

<!-- Header -->
<div class="page-header">
    <div>
        <h1 style="font-size:24px; font-weight:700; margin:0 0 6px;">
            <i class="fas fa-file-invoice me-2"></i>Mes Commandes
        </h1>
        <p style="margin:0; opacity:0.85; font-size:14px;">{{ $fournisseur->nom }}</p>
    </div>
    <div style="text-align:right;">
        <div style="font-size:13px; opacity:0.7; margin-bottom:2px;">Total reçu</div>
        <div style="font-size:22px; font-weight:700; font-family:monospace;">
            {{ number_format($fournisseur->commandes()->where('statut','finalisee')->sum('montant_total'), 0, ',', ' ') }} GNF
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-4">
    <i class="fas fa-times-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Filtres -->
<div class="filter-card">
    <form method="GET" action="{{ route('fournisseur.espace.commandes') }}">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold small">Rechercher</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="N° commande, pharmacie..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold small">Statut</label>
                <select name="statut" class="form-select">
                    <option value="">Tous</option>
                    <option value="en_attente"    {{ request('statut')=='en_attente'    ? 'selected':'' }}>En attente</option>
                    <option value="envoyee"       {{ request('statut')=='envoyee'       ? 'selected':'' }}>Nouvelle</option>
                    <option value="en_traitement" {{ request('statut')=='en_traitement' ? 'selected':'' }}>En traitement</option>
                    <option value="expediee"      {{ request('statut')=='expediee'      ? 'selected':'' }}>Expédiée</option>
                    <option value="finalisee"     {{ request('statut')=='finalisee'     ? 'selected':'' }}>Finalisée</option>
                    <option value="annulee"       {{ request('statut')=='annulee'       ? 'selected':'' }}>Annulée</option>
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
            <div class="col-6 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="fas fa-filter me-1"></i>Filtrer
                </button>
                <a href="{{ route('fournisseur.espace.commandes') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Tableau -->
<div class="table-card">
    <div style="padding: 16px 24px; border-bottom: 1px solid #F3F4F6; display:flex; align-items:center; justify-content:space-between;">
        <h6 class="mb-0 fw-semibold" style="color:#374151;">
            {{ $commandes->total() }} commande(s)
        </h6>
        <div class="d-flex gap-2 flex-wrap">
            @foreach(['envoyee'=>'Nouvelles','en_traitement'=>'En traitement','expediee'=>'Expédiées'] as $s => $l)
            @php $nb = $fournisseur->commandes()->where('statut',$s)->count(); @endphp
            @if($nb > 0)
            <span class="badge-statut {{ $s }}">{{ $nb }} {{ $l }}</span>
            @endif
            @endforeach
        </div>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Commande</th>
                    <th>Pharmacie</th>
                    <th>Produits</th>
                    <th>Livraison prévue</th>
                    <th>Montant</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($commandes as $cmd)
                <tr>
                    <td>
                        <div style="font-family:monospace; font-size:13px; font-weight:700; color:#1E3A8A; background:#EFF6FF; padding:3px 8px; border-radius:6px; display:inline-block;">
                            {{ $cmd->numero_commande }}
                        </div>
                        <div style="font-size:12px; color:#9CA3AF; margin-top:3px;">
                            {{ $cmd->created_at->format('d/m/Y') }}
                        </div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="pharmacie-avatar">
                                {{ strtoupper(substr($cmd->pharmacie->nom ?? 'P', 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-weight:600; font-size:14px; color:#1F2937;">{{ $cmd->pharmacie->nom ?? '—' }}</div>
                                <div style="font-size:12px; color:#9CA3AF;">{{ $cmd->pharmacie->ville ?? '' }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span style="font-weight:700; color:#374151;">{{ $cmd->lignes->count() }}</span>
                        <span style="font-size:12px; color:#9CA3AF;"> ligne(s)</span>
                    </td>
                    <td>
                        @if($cmd->date_livraison_prevue)
                        @php $date = \Carbon\Carbon::parse($cmd->date_livraison_prevue); @endphp
                        <div style="font-weight:600; font-size:13px; color:{{ $date->isPast() && !in_array($cmd->statut,['finalisee','annulee']) ? '#EF4444' : '#374151' }};">
                            {{ $date->format('d/m/Y') }}
                        </div>
                        @if($date->isPast() && !in_array($cmd->statut,['finalisee','annulee']))
                        <div style="font-size:11px; color:#EF4444; font-weight:600;">En retard</div>
                        @else
                        <div style="font-size:11px; color:#9CA3AF;">{{ $date->diffForHumans() }}</div>
                        @endif
                        @else
                        <span style="color:#9CA3AF; font-size:13px;">—</span>
                        @endif
                    </td>
                    <td>
                        @if($cmd->montant_total > 0)
                        <div style="font-weight:700; font-family:monospace; color:#1E3A8A; font-size:13px;">
                            {{ number_format($cmd->montant_total, 0, ',', ' ') }} GNF
                        </div>
                        @else
                        <span style="font-size:12px; color:#9CA3AF;">À définir</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge-statut {{ $cmd->statut }}">
                            <span class="statut-dot dot-{{ $cmd->statut }}"></span>
                            @if($cmd->statut=='en_attente') En attente
                            @elseif($cmd->statut=='envoyee') Nouvelle
                            @elseif($cmd->statut=='en_traitement') En traitement
                            @elseif($cmd->statut=='expediee') Expédiée
                            @elseif($cmd->statut=='finalisee') Finalisée
                            @else Annulée
                            @endif
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('fournisseur.espace.commande.show', $cmd) }}" class="btn-action btn-voir">
                                <i class="fas fa-eye me-1"></i>Voir
                            </a>
                            @if($cmd->statut == 'envoyee')
                            <form method="POST" action="{{ route('fournisseur.espace.commande.statut', $cmd) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="statut" value="en_traitement">
                                <button class="btn-action btn-confirmer">
                                    <i class="fas fa-check me-1"></i>Confirmer
                                </button>
                            </form>
                            @elseif($cmd->statut == 'en_traitement')
                            <form method="POST" action="{{ route('fournisseur.espace.commande.statut', $cmd) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="statut" value="expediee">
                                <button class="btn-action btn-expedier">
                                    <i class="fas fa-truck me-1"></i>Expédier
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div style="text-align:center; padding:60px 20px; color:#9CA3AF;">
                            <i class="fas fa-file-invoice" style="font-size:48px; opacity:0.25; display:block; margin-bottom:16px;"></i>
                            <p class="mb-0 fw-semibold">Aucune commande trouvée</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($commandes->hasPages())
    <div style="padding:16px 24px; border-top:1px solid #F3F4F6;">
        {{ $commandes->links() }}
    </div>
    @endif
</div>

@endsection
