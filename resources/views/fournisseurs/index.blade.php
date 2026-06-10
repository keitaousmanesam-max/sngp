@extends('layouts.app')

@section('title', 'Gestion des Fournisseurs')

@push('styles')
<style>
    .stats-card { background: white; border-radius: 12px; padding: 20px 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; border-left: 4px solid transparent; transition: all 0.3s; }
    .stats-card:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0,0,0,0.1); }
    .stats-card.total { border-left-color: #3B82F6; }
    .stats-card.valide { border-left-color: #10B981; }
    .stats-card.attente { border-left-color: #F59E0B; }
    .stats-card.suspendu { border-left-color: #EF4444; }
    .stats-value { font-size: 32px; font-weight: 700; line-height: 1; }
    .stats-label { font-size: 13px; color: #6B7280; margin-top: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
    .filter-card { background: white; border-radius: 12px; padding: 20px 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; margin-bottom: 24px; }
    .table-card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; overflow: hidden; }
    .table-card .table { margin-bottom: 0; }
    .table-card thead { background: #F9FAFB; }
    .table-card thead th { border: none; color: #6B7280; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; padding: 14px 20px; }
    .table-card tbody td { padding: 14px 20px; vertical-align: middle; border-color: #F3F4F6; font-size: 14px; }
    .table-card tbody tr:hover { background: #F9FAFB; }
    .fournisseur-avatar { width: 42px; height: 42px; border-radius: 10px; background: linear-gradient(135deg, #059669, #10B981); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 16px; flex-shrink: 0; }
    .badge-statut { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; }
    .badge-statut.valide { background: #D1FAE5; color: #065F46; }
    .badge-statut.en_attente { background: #FEF3C7; color: #92400E; }
    .badge-statut.suspendu { background: #FEE2E2; color: #991B1B; }
    .badge-statut.rejete { background: #F3F4F6; color: #374151; }
    .action-btn { width: 30px; height: 30px; border-radius: 7px; border: none; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; transition: all 0.2s; cursor: pointer; }
    .action-btn:hover { opacity: 0.8; transform: scale(1.1); }
    .action-btn.view { background: #EFF6FF; color: #2563EB; }
    .action-btn.edit { background: #F0FDF4; color: #16A34A; }
    .action-btn.validate { background: #D1FAE5; color: #065F46; }
    .action-btn.reject { background: #FEE2E2; color: #991B1B; }
    .action-btn.suspend { background: #FFFBEB; color: #D97706; }
    .action-btn.activate { background: #F0FDF4; color: #16A34A; }
    .action-btn.delete { background: #FEF2F2; color: #DC2626; }
</style>
@endpush

@section('content')

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-truck me-2"></i>Fournisseurs</h1>
        <p class="text-muted mb-0">Gestion des fournisseurs pharmaceutiques agréés</p>
    </div>
    <a href="{{ route('fournisseurs.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nouveau Fournisseur
    </a>
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

<!-- Statistiques -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stats-card total">
            <div class="stats-value" style="color: #3B82F6;">{{ $stats['total'] }}</div>
            <div class="stats-label"><i class="fas fa-truck me-1"></i>Total</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stats-card valide">
            <div class="stats-value" style="color: #10B981;">{{ $stats['valides'] }}</div>
            <div class="stats-label"><i class="fas fa-check-circle me-1"></i>Validés</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stats-card attente">
            <div class="stats-value" style="color: #F59E0B;">{{ $stats['en_attente'] }}</div>
            <div class="stats-label"><i class="fas fa-clock me-1"></i>En Attente</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stats-card suspendu">
            <div class="stats-value" style="color: #EF4444;">{{ $stats['suspendus'] }}</div>
            <div class="stats-label"><i class="fas fa-ban me-1"></i>Suspendus</div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="filter-card">
    <form method="GET" action="{{ route('fournisseurs.index') }}">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-5">
                <label class="form-label fw-semibold small">Rechercher</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Nom, email, ville..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label fw-semibold small">Statut</label>
                <select name="statut" class="form-select">
                    <option value="">Tous</option>
                    <option value="en_attente" {{ request('statut') == 'en_attente' ? 'selected' : '' }}>En attente</option>
                    <option value="valide" {{ request('statut') == 'valide' ? 'selected' : '' }}>Validé</option>
                    <option value="suspendu" {{ request('statut') == 'suspendu' ? 'selected' : '' }}>Suspendu</option>
                    <option value="rejete" {{ request('statut') == 'rejete' ? 'selected' : '' }}>Rejeté</option>
                </select>
            </div>
            <div class="col-6 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="fas fa-filter me-1"></i>Filtrer
                </button>
                <a href="{{ route('fournisseurs.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Tableau -->
<div class="table-card">
    <div style="padding: 20px 24px; border-bottom: 1px solid #F3F4F6;">
        <h6 class="mb-0 fw-semibold" style="color: #374151;">{{ $fournisseurs->total() }} fournisseur(s) trouvé(s)</h6>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Fournisseur</th>
                    <th>Contact</th>
                    <th>Localisation</th>
                    <th>Commandes</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($fournisseurs as $fournisseur)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="fournisseur-avatar">
                                {{ strtoupper(substr($fournisseur->nom, 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-semibold" style="color: #1F2937;">{{ $fournisseur->nom }}</div>
                                @if($fournisseur->numero_registre)
                                <small class="text-muted">Reg: {{ $fournisseur->numero_registre }}</small>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <div><i class="fas fa-phone me-1 text-muted"></i>{{ $fournisseur->telephone }}</div>
                        <small class="text-muted"><i class="fas fa-envelope me-1"></i>{{ $fournisseur->email }}</small>
                    </td>
                    <td>
                        <div>{{ $fournisseur->ville ?? '—' }}</div>
                        <small class="text-muted">{{ $fournisseur->pays }}</small>
                    </td>
                    <td>
                        <span class="fw-semibold">{{ $fournisseur->commandes_count }}</span>
                        <small class="text-muted"> commande(s)</small>
                    </td>
                    <td>
                        <span class="badge-statut {{ $fournisseur->statut }}">
                            @if($fournisseur->statut == 'valide')
                                <i class="fas fa-check-circle me-1"></i>Validé
                            @elseif($fournisseur->statut == 'en_attente')
                                <i class="fas fa-clock me-1"></i>En attente
                            @elseif($fournisseur->statut == 'suspendu')
                                <i class="fas fa-ban me-1"></i>Suspendu
                            @else
                                <i class="fas fa-times-circle me-1"></i>Rejeté
                            @endif
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1 flex-wrap">
                            <a href="{{ route('fournisseurs.show', $fournisseur) }}" class="action-btn view" title="Voir">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('fournisseurs.edit', $fournisseur) }}" class="action-btn edit" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if($fournisseur->statut == 'en_attente')
                            <form method="POST" action="{{ route('fournisseurs.valider', $fournisseur) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <button class="action-btn validate" title="Valider" onclick="return confirm('Valider ce fournisseur ?')">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('fournisseurs.rejeter', $fournisseur) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <button class="action-btn reject" title="Rejeter" onclick="return confirm('Rejeter ce fournisseur ?')">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                            @elseif($fournisseur->statut == 'valide')
                            <form method="POST" action="{{ route('fournisseurs.suspendre', $fournisseur) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <button class="action-btn suspend" title="Suspendre" onclick="return confirm('Suspendre ce fournisseur ?')">
                                    <i class="fas fa-pause"></i>
                                </button>
                            </form>
                            @elseif($fournisseur->statut == 'suspendu')
                            <form method="POST" action="{{ route('fournisseurs.reactiver', $fournisseur) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <button class="action-btn activate" title="Réactiver">
                                    <i class="fas fa-play"></i>
                                </button>
                            </form>
                            @endif
                            <form method="POST" action="{{ route('fournisseurs.destroy', $fournisseur) }}" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="action-btn delete" title="Supprimer" onclick="return confirm('Supprimer ce fournisseur ?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div style="text-align: center; padding: 60px 20px; color: #9CA3AF;">
                            <i class="fas fa-truck" style="font-size: 48px; opacity: 0.3; display: block; margin-bottom: 16px;"></i>
                            <p class="mb-2 fw-semibold">Aucun fournisseur trouvé</p>
                            <a href="{{ route('fournisseurs.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i>Ajouter un fournisseur
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($fournisseurs->hasPages())
    <div style="padding: 16px 24px; border-top: 1px solid #F3F4F6;">
        {{ $fournisseurs->links() }}
    </div>
    @endif
</div>

{{-- Modal Credentials Fournisseur --}}
@if(session('nouveau_utilisateur'))
@php $creds = session('nouveau_utilisateur'); @endphp
<div id="modalCredentials" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 9999; display: flex; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 20px; width: 100%; max-width: 460px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.3); margin: 20px;">
        <div style="background: linear-gradient(135deg, #059669, #10B981); padding: 28px; text-align: center;">
            <div style="width: 64px; height: 64px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                <i class="fas fa-truck" style="font-size: 28px; color: white;"></i>
            </div>
            <h4 style="color: white; margin: 0; font-weight: 700;">Fournisseur Validé !</h4>
            <p style="color: rgba(255,255,255,0.8); margin: 6px 0 0; font-size: 14px;">{{ $creds['nom_complet'] }}</p>
        </div>
        <div style="padding: 24px;">
            <p style="font-size: 14px; color: #374151; margin-bottom: 16px; font-weight: 600;">
                <i class="fas fa-key me-2" style="color: #10B981;"></i>Identifiants de connexion :
            </p>
            <div style="background: #F9FAFB; border-radius: 10px; padding: 14px 16px; margin-bottom: 10px; border: 1px solid #E5E7EB;">
                <div style="font-size: 11px; color: #9CA3AF; text-transform: uppercase; margin-bottom: 4px;">Email</div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 14px; font-weight: 700; color: #1F2937;" id="credEmail">{{ $creds['email'] }}</span>
                    <button onclick="copier('credEmail', this)" style="background: #ECFDF5; color: #065F46; border: none; border-radius: 6px; font-size: 12px; padding: 5px 10px; cursor: pointer;">
                        <i class="fas fa-copy me-1"></i>Copier
                    </button>
                </div>
            </div>
            <div style="background: #065F46; border-radius: 10px; padding: 14px 16px; margin-bottom: 16px;">
                <div style="font-size: 11px; color: rgba(255,255,255,0.6); text-transform: uppercase; margin-bottom: 4px;">Mot de passe temporaire</div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 20px; font-weight: 700; color: white; font-family: monospace; letter-spacing: 2px;" id="credPassword">{{ $creds['mot_de_passe'] }}</span>
                    <button onclick="copier('credPassword', this)" style="background: rgba(255,255,255,0.2); color: white; border: none; border-radius: 6px; font-size: 12px; padding: 5px 10px; cursor: pointer;">
                        <i class="fas fa-copy me-1"></i>Copier
                    </button>
                </div>
            </div>
            <div style="background: #FFFBEB; border: 1px solid #FCD34D; border-radius: 10px; padding: 12px 16px; margin-bottom: 20px;">
                <p style="color: #92400E; font-size: 12px; margin: 0;">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Transmettez ces identifiants au fournisseur. Le mot de passe devra être changé à la première connexion.
                </p>
            </div>
            <button onclick="fermerModal()" style="background: linear-gradient(135deg, #059669, #10B981); color: white; border: none; border-radius: 10px; padding: 14px; width: 100%; font-size: 15px; font-weight: 600; cursor: pointer;">
                <i class="fas fa-check me-2"></i>J'ai noté les identifiants
            </button>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
    function copier(elementId, btn) {
        const text = document.getElementById(elementId).innerText;
        navigator.clipboard.writeText(text).then(() => {
            const original = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check me-1"></i>Copié !';
            setTimeout(() => { btn.innerHTML = original; }, 2000);
        });
    }
    function fermerModal() {
        document.getElementById('modalCredentials').style.display = 'none';
        fetch('{{ route("utilisateurs.clear-session") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
        });
    }
</script>
@endpush