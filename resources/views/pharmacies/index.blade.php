@extends('layouts.app')

@section('title', 'Gestion des Pharmacies')

@push('styles')
<style>
    .stats-card {
        background: white;
        border-radius: 12px;
        padding: 20px 24px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        border: 1px solid #f0f0f0;
        border-left: 4px solid transparent;
        transition: all 0.3s;
    }
    .stats-card:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0,0,0,0.1); }
    .stats-card.total { border-left-color: #3B82F6; }
    .stats-card.active { border-left-color: #10B981; }
    .stats-card.suspended { border-left-color: #F59E0B; }
    .stats-card.closed { border-left-color: #EF4444; }
    .stats-value { font-size: 32px; font-weight: 700; line-height: 1; }
    .stats-label { font-size: 13px; color: #6B7280; margin-top: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
    .filter-card { background: white; border-radius: 12px; padding: 20px 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; margin-bottom: 24px; }
    .table-card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; overflow: hidden; }
    .table-card .table { margin-bottom: 0; }
    .table-card thead { background: #F9FAFB; }
    .table-card thead th { border: none; color: #6B7280; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; padding: 14px 20px; }
    .table-card tbody td { padding: 16px 20px; vertical-align: middle; border-color: #F3F4F6; font-size: 14px; }
    .table-card tbody tr:hover { background: #F9FAFB; }
    .badge-statut { padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; }
    .badge-statut.active { background: #D1FAE5; color: #065F46; }
    .badge-statut.suspendue { background: #FEF3C7; color: #92400E; }
    .badge-statut.fermee { background: #FEE2E2; color: #991B1B; }
    .action-btn { width: 32px; height: 32px; border-radius: 8px; border: none; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; transition: all 0.2s; cursor: pointer; }
    .action-btn.view { background: #EFF6FF; color: #2563EB; }
    .action-btn.edit { background: #F0FDF4; color: #16A34A; }
    .action-btn.suspend { background: #FFFBEB; color: #D97706; }
    .action-btn.activate { background: #F0FDF4; color: #16A34A; }
    .action-btn.delete { background: #FEF2F2; color: #DC2626; }
    .action-btn:hover { opacity: 0.8; transform: scale(1.1); }
    .pharmacie-avatar { width: 42px; height: 42px; border-radius: 10px; background: linear-gradient(135deg, #1B4F8A, #2E75B6); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 16px; flex-shrink: 0; }
</style>
@endpush

@section('content')

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-hospital me-2"></i>Pharmacies Agréées</h1>
        <p class="text-muted mb-0">Gestion des pharmacies enregistrées sur le territoire national</p>
    </div>
    <a href="{{ route('pharmacies.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nouvelle Pharmacie
    </a>
</div>

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
    <i class="fas fa-times-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Statistiques -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stats-card total">
            <div class="stats-value" style="color: #3B82F6;">{{ $stats['total'] }}</div>
            <div class="stats-label"><i class="fas fa-hospital me-1"></i>Total Pharmacies</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stats-card active">
            <div class="stats-value" style="color: #10B981;">{{ $stats['actives'] }}</div>
            <div class="stats-label"><i class="fas fa-check-circle me-1"></i>Actives</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stats-card suspended">
            <div class="stats-value" style="color: #F59E0B;">{{ $stats['suspendues'] }}</div>
            <div class="stats-label"><i class="fas fa-pause-circle me-1"></i>Suspendues</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stats-card closed">
            <div class="stats-value" style="color: #EF4444;">{{ $stats['fermees'] }}</div>
            <div class="stats-label"><i class="fas fa-times-circle me-1"></i>Fermées</div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="filter-card">
    <form method="GET" action="{{ route('pharmacies.index') }}">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold small">Rechercher</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Nom, agrément, région..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold small">Statut</label>
                <select name="statut" class="form-select">
                    <option value="">Tous</option>
                    <option value="active" {{ request('statut') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="suspendue" {{ request('statut') == 'suspendue' ? 'selected' : '' }}>Suspendue</option>
                    <option value="fermee" {{ request('statut') == 'fermee' ? 'selected' : '' }}>Fermée</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label fw-semibold small">Région</label>
                <select name="region" class="form-select">
                    <option value="">Toutes les régions</option>
                    @foreach($regions as $region)
                    <option value="{{ $region }}" {{ request('region') == $region ? 'selected' : '' }}>{{ $region }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="fas fa-filter me-1"></i>Filtrer
                </button>
                <a href="{{ route('pharmacies.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Tableau -->
<div class="table-card">
    <div style="padding: 20px 24px; border-bottom: 1px solid #F3F4F6;">
        <h6 class="mb-0 fw-semibold" style="color: #374151;">
            {{ $pharmacies->total() }} pharmacie(s) trouvée(s)
        </h6>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Pharmacie</th>
                    <th>Agrément</th>
                    <th>Localisation</th>
                    <th>Contact</th>
                    <th>Employés</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pharmacies as $pharmacie)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="pharmacie-avatar">{{ strtoupper(substr($pharmacie->nom, 0, 1)) }}</div>
                            <div>
                                <div class="fw-semibold" style="color: #1F2937;">{{ $pharmacie->nom }}</div>
                                <small class="text-muted">Depuis {{ $pharmacie->date_agrement->format('d/m/Y') }}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span style="background: #F3F4F6; padding: 4px 10px; border-radius: 6px; font-size: 13px; font-family: monospace;">
                            {{ $pharmacie->numero_agrement }}
                        </span>
                    </td>
                    <td>
                        <div><i class="fas fa-map-marker-alt me-1 text-muted"></i>{{ $pharmacie->prefecture }}</div>
                        <small class="text-muted">{{ $pharmacie->region }}</small>
                    </td>
                    <td>
                        <div><i class="fas fa-phone me-1 text-muted"></i>{{ $pharmacie->telephone }}</div>
                        <small class="text-muted"><i class="fas fa-envelope me-1"></i>{{ $pharmacie->email }}</small>
                    </td>
                    <td>
                        <span class="fw-semibold">{{ $pharmacie->utilisateurs_count }}</span>
                        <small class="text-muted"> employé(s)</small>
                    </td>
                    <td>
                        <span class="badge-statut {{ $pharmacie->statut }}">
                            @if($pharmacie->statut == 'active')
                                <i class="fas fa-check-circle me-1"></i>Active
                            @elseif($pharmacie->statut == 'suspendue')
                                <i class="fas fa-pause-circle me-1"></i>Suspendue
                            @else
                                <i class="fas fa-times-circle me-1"></i>Fermée
                            @endif
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('pharmacies.show', $pharmacie) }}" class="action-btn view" title="Voir détails">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('pharmacies.edit', $pharmacie) }}" class="action-btn edit" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if($pharmacie->statut == 'active')
                            <form method="POST" action="{{ route('pharmacies.suspendre', $pharmacie) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="action-btn suspend" title="Suspendre"
                                    onclick="return confirm('Suspendre cette pharmacie ?')">
                                    <i class="fas fa-pause"></i>
                                </button>
                            </form>
                            @else
                            <form method="POST" action="{{ route('pharmacies.reactiver', $pharmacie) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="action-btn activate" title="Réactiver">
                                    <i class="fas fa-play"></i>
                                </button>
                            </form>
                            @endif
                            <form method="POST" action="{{ route('pharmacies.destroy', $pharmacie) }}" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="action-btn delete" title="Supprimer"
                                    onclick="return confirm('Supprimer définitivement cette pharmacie ?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div style="text-align: center; padding: 60px 20px; color: #9CA3AF;">
                            <i class="fas fa-hospital" style="font-size: 48px; opacity: 0.3; display: block; margin-bottom: 16px;"></i>
                            <p class="mb-2 fw-semibold">Aucune pharmacie trouvée</p>
                            <a href="{{ route('pharmacies.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i>Ajouter une pharmacie
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($pharmacies->hasPages())
    <div style="padding: 16px 24px; border-top: 1px solid #F3F4F6;">
        {{ $pharmacies->links() }}
    </div>
    @endif
</div>

{{-- ===== MODAL CREDENTIALS ===== --}}
@if(session('nouvelle_pharmacie'))
@php $creds = session('nouvelle_pharmacie'); @endphp
<div id="modalCredentials" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 9999; display: flex; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 20px; width: 100%; max-width: 480px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.3); margin: 20px;">

        <!-- Header -->
        <div style="background: linear-gradient(135deg, #1E3A8A, #3B82F6); padding: 32px; text-align: center;">
            <div style="width: 72px; height: 72px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                <i class="fas fa-check" style="font-size: 32px; color: white;"></i>
            </div>
            <h4 style="color: white; margin: 0; font-weight: 700; font-size: 22px;">Pharmacie Créée avec Succès !</h4>
            <p style="color: rgba(255,255,255,0.8); margin: 8px 0 0; font-size: 15px;">{{ $creds['nom'] }}</p>
        </div>

        <!-- Body -->
        <div style="padding: 28px;">

            @if($creds['email_envoye'])
            <div style="background: #D1FAE5; border: 1px solid #6EE7B7; border-radius: 10px; padding: 14px 18px; margin-bottom: 20px;">
                <p style="color: #065F46; font-size: 13px; margin: 0;">
                    <i class="fas fa-envelope me-2"></i>
                    Email envoyé avec succès à <strong>{{ $creds['email'] }}</strong>
                </p>
            </div>
            @else
            <div style="background: #FEF3C7; border: 1px solid #FCD34D; border-radius: 10px; padding: 14px 18px; margin-bottom: 20px;">
                <p style="color: #92400E; font-size: 13px; margin: 0;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Email non envoyé. Notez bien les identifiants ci-dessous.
                </p>
            </div>
            @endif

            <p style="font-size: 14px; color: #374151; margin-bottom: 16px; font-weight: 600;">
                <i class="fas fa-key me-2" style="color: #3B82F6;"></i>Identifiants de connexion :
            </p>

            <!-- Email -->
            <div style="background: #F9FAFB; border-radius: 10px; padding: 14px 16px; margin-bottom: 12px; border: 1px solid #E5E7EB;">
                <div style="font-size: 11px; color: #9CA3AF; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">Adresse Email</div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 15px; font-weight: 700; color: #1F2937;" id="credEmail">{{ $creds['email'] }}</span>
                    <button onclick="copier('credEmail', this)" style="background: #EFF6FF; color: #2563EB; border: none; border-radius: 6px; font-size: 12px; padding: 6px 12px; cursor: pointer; font-weight: 600;">
                        <i class="fas fa-copy me-1"></i>Copier
                    </button>
                </div>
            </div>

            <!-- Mot de passe -->
            <div style="background: #1E3A8A; border-radius: 10px; padding: 16px; margin-bottom: 20px;">
                <div style="font-size: 11px; color: rgba(255,255,255,0.6); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">Mot de passe temporaire</div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 22px; font-weight: 700; color: white; font-family: monospace; letter-spacing: 3px;" id="credPassword">{{ $creds['mot_de_passe'] }}</span>
                    <button onclick="copier('credPassword', this)" style="background: rgba(255,255,255,0.2); color: white; border: none; border-radius: 6px; font-size: 12px; padding: 6px 12px; cursor: pointer; font-weight: 600;">
                        <i class="fas fa-copy me-1"></i>Copier
                    </button>
                </div>
            </div>

            <div style="background: #FFFBEB; border: 1px solid #FCD34D; border-radius: 10px; padding: 12px 16px; margin-bottom: 24px;">
                <p style="color: #92400E; font-size: 12px; margin: 0;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Important :</strong> Transmettez ces identifiants au responsable. Le mot de passe devra être changé à la première connexion.
                </p>
            </div>

            <button onclick="fermerModal()" style="background: linear-gradient(135deg, #1E3A8A, #3B82F6); color: white; border: none; border-radius: 10px; padding: 14px; width: 100%; font-size: 16px; font-weight: 600; cursor: pointer;">
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
            btn.style.background = '#D1FAE5';
            btn.style.color = '#065F46';
            setTimeout(() => {
                btn.innerHTML = original;
                btn.style.background = '';
                btn.style.color = '';
            }, 2000);
        });
    }

    function fermerModal() {
        document.getElementById('modalCredentials').style.display = 'none';
        fetch('{{ route("pharmacies.clear-session") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        });
    }
</script>
@endpush