@extends('layouts.app')

@section('title', 'Gestion des Utilisateurs')

@push('styles')
<style>
    .stats-card { background: white; border-radius: 12px; padding: 20px 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; border-left: 4px solid transparent; transition: all 0.3s; }
    .stats-card:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0,0,0,0.1); }
    .stats-card.total { border-left-color: #3B82F6; }
    .stats-card.actif { border-left-color: #10B981; }
    .stats-card.inactif { border-left-color: #F59E0B; }
    .stats-card.bloque { border-left-color: #EF4444; }
    .stats-value { font-size: 32px; font-weight: 700; line-height: 1; }
    .stats-label { font-size: 13px; color: #6B7280; margin-top: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
    .filter-card { background: white; border-radius: 12px; padding: 20px 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; margin-bottom: 24px; }
    .table-card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; overflow: hidden; }
    .table-card .table { margin-bottom: 0; }
    .table-card thead { background: #F9FAFB; }
    .table-card thead th { border: none; color: #6B7280; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; padding: 14px 20px; }
    .table-card tbody td { padding: 14px 20px; vertical-align: middle; border-color: #F3F4F6; font-size: 14px; }
    .table-card tbody tr:hover { background: #F9FAFB; }
    .user-avatar { width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, #1B4F8A, #2E75B6); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 14px; flex-shrink: 0; }
    .badge-role { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; }
    .badge-role.admin_national { background: #EDE9FE; color: #5B21B6; }
    .badge-role.admin_pharmacie { background: #DBEAFE; color: #1E40AF; }
    .badge-role.pharmacien { background: #D1FAE5; color: #065F46; }
    .badge-role.caissier { background: #FEF3C7; color: #92400E; }
    .badge-role.gestionnaire_stock { background: #FFE4E6; color: #9F1239; }
    .badge-role.assistant_pharmacien { background: #E0F2FE; color: #075985; }
    .badge-role.fournisseur { background: #F3F4F6; color: #374151; }
    .badge-statut { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; }
    .badge-statut.actif { background: #D1FAE5; color: #065F46; }
    .badge-statut.inactif { background: #FEF3C7; color: #92400E; }
    .badge-statut.bloque { background: #FEE2E2; color: #991B1B; }
    .action-btn { width: 30px; height: 30px; border-radius: 7px; border: none; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; transition: all 0.2s; cursor: pointer; }
    .action-btn:hover { opacity: 0.8; transform: scale(1.1); }
    .action-btn.view { background: #EFF6FF; color: #2563EB; }
    .action-btn.edit { background: #F0FDF4; color: #16A34A; }
    .action-btn.activate { background: #F0FDF4; color: #16A34A; }
    .action-btn.deactivate { background: #FFFBEB; color: #D97706; }
    .action-btn.unlock { background: #EFF6FF; color: #2563EB; }
    .action-btn.reset { background: #F5F3FF; color: #7C3AED; }
    .action-btn.delete { background: #FEF2F2; color: #DC2626; }
    .admin-badge { background: linear-gradient(135deg, #EDE9FE, #DDD6FE); border: 1px solid #C4B5FD; border-radius: 8px; padding: 4px 10px; font-size: 11px; color: #5B21B6; font-weight: 600; }
</style>
@endpush

@section('content')

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1">
            <i class="fas fa-users me-2"></i>
            @role('admin_national') Gestion des Utilisateurs @else Gestion des Employés @endrole
        </h1>
        <p class="text-muted mb-0">
            @role('admin_national') Tous les utilisateurs du système @else Employés de votre pharmacie @endrole
        </p>
    </div>
    <a href="{{ route('utilisateurs.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nouvel Utilisateur
    </a>
</div>

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-4">
    <i class="fas fa-times-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
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
            <div class="stats-label"><i class="fas fa-users me-1"></i>Total</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stats-card actif">
            <div class="stats-value" style="color: #10B981;">{{ $stats['actifs'] }}</div>
            <div class="stats-label"><i class="fas fa-check-circle me-1"></i>Actifs</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stats-card inactif">
            <div class="stats-value" style="color: #F59E0B;">{{ $stats['inactifs'] }}</div>
            <div class="stats-label"><i class="fas fa-pause-circle me-1"></i>Inactifs</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stats-card bloque">
            <div class="stats-value" style="color: #EF4444;">{{ $stats['bloques'] }}</div>
            <div class="stats-label"><i class="fas fa-lock me-1"></i>Bloqués</div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="filter-card">
    <form method="GET" action="{{ route('utilisateurs.index') }}">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label fw-semibold small">Rechercher</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Nom, prénom, email..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold small">Rôle</label>
                <select name="role" class="form-select">
                    <option value="">Tous</option>
                    @foreach($roles as $role)
                    <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold small">Statut</label>
                <select name="statut" class="form-select">
                    <option value="">Tous</option>
                    <option value="actif" {{ request('statut') == 'actif' ? 'selected' : '' }}>Actif</option>
                    <option value="inactif" {{ request('statut') == 'inactif' ? 'selected' : '' }}>Inactif</option>
                </select>
            </div>
            @role('admin_national')
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold small">Pharmacie</label>
                <select name="pharmacie_id" class="form-select">
                    <option value="">Toutes</option>
                    @foreach($pharmacies as $pharmacie)
                    <option value="{{ $pharmacie->id }}" {{ request('pharmacie_id') == $pharmacie->id ? 'selected' : '' }}>
                        {{ $pharmacie->nom }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endrole
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="fas fa-filter me-1"></i>Filtrer
                </button>
                <a href="{{ route('utilisateurs.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Tableau -->
<div class="table-card">
    <div style="padding: 20px 24px; border-bottom: 1px solid #F3F4F6;">
        <h6 class="mb-0 fw-semibold" style="color: #374151;">{{ $utilisateurs->total() }} utilisateur(s) trouvé(s)</h6>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Utilisateur</th>
                    <th>Rôle</th>
                    @role('admin_national')<th>Pharmacie</th>@endrole
                    <th>Contact</th>
                    <th>Dernière connexion</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($utilisateurs as $utilisateur)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="user-avatar">
                                {{ strtoupper(substr($utilisateur->prenom, 0, 1)) }}{{ strtoupper(substr($utilisateur->nom, 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-semibold" style="color: #1F2937;">
                                    {{ $utilisateur->prenom }} {{ $utilisateur->nom }}
                                    @if($utilisateur->hasRole('admin_national') && $nbAdminsNationaux <= 1)
                                    <span class="admin-badge ms-1"><i class="fas fa-shield-alt me-1"></i>Dernier admin</span>
                                    @endif
                                </div>
                                <small class="text-muted">{{ $utilisateur->email }}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        @php $role = $utilisateur->getRoleNames()->first() ?? 'aucun'; @endphp
                        <span class="badge-role {{ $role }}">
                            {{ ucfirst(str_replace('_', ' ', $role)) }}
                        </span>
                    </td>
                    @role('admin_national')
                    <td>
                        @if($utilisateur->pharmacie)
                            <span style="font-size: 13px;">{{ $utilisateur->pharmacie->nom }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    @endrole
                    <td>
                        <div style="font-size: 13px;"><i class="fas fa-phone me-1 text-muted"></i>{{ $utilisateur->telephone ?? '—' }}</div>
                    </td>
                    <td>
                        <span style="font-size: 13px; color: #6B7280;">
                            {{ $utilisateur->derniere_connexion ? $utilisateur->derniere_connexion->diffForHumans() : 'Jamais' }}
                        </span>
                    </td>
                    <td>
                        @if($utilisateur->bloque_le)
                            <span class="badge-statut bloque"><i class="fas fa-lock me-1"></i>Bloqué</span>
                        @elseif($utilisateur->actif)
                            <span class="badge-statut actif"><i class="fas fa-check-circle me-1"></i>Actif</span>
                        @else
                            <span class="badge-statut inactif"><i class="fas fa-pause-circle me-1"></i>Inactif</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1 flex-wrap">
                            {{-- Voir : toujours disponible --}}
                            <a href="{{ route('utilisateurs.show', $utilisateur) }}" class="action-btn view" title="Voir">
                                <i class="fas fa-eye"></i>
                            </a>

                            @php $estDernierAdmin = $utilisateur->hasRole('admin_national') && $nbAdminsNationaux <= 1; @endphp

                            @if(!$utilisateur->hasRole('admin_national'))
                            {{-- Modifier --}}
                            <a href="{{ route('utilisateurs.edit', $utilisateur) }}" class="action-btn edit" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endif

                            {{-- Débloquer --}}
                            @if($utilisateur->bloque_le)
                            <form method="POST" action="{{ route('utilisateurs.debloquer', $utilisateur) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <button class="action-btn unlock" title="Débloquer">
                                    <i class="fas fa-unlock"></i>
                                </button>
                            </form>
                            @endif

                            @if(!$utilisateur->hasRole('admin_national'))
                            {{-- Activer / Désactiver --}}
                            @if($utilisateur->actif)
                            <form method="POST" action="{{ route('utilisateurs.desactiver', $utilisateur) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <button class="action-btn deactivate" title="Désactiver"
                                    onclick="return confirm('Désactiver ce compte ?')">
                                    <i class="fas fa-pause"></i>
                                </button>
                            </form>
                            @else
                            <form method="POST" action="{{ route('utilisateurs.activer', $utilisateur) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <button class="action-btn activate" title="Activer">
                                    <i class="fas fa-play"></i>
                                </button>
                            </form>
                            @endif
                            @endif

                            {{-- Réinitialiser mot de passe --}}
                            <form method="POST" action="{{ route('utilisateurs.reinitialiser-mot-de-passe', $utilisateur) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <button class="action-btn reset" title="Réinitialiser mot de passe"
                                    onclick="return confirm('Réinitialiser le mot de passe de {{ $utilisateur->prenom }} ?')">
                                    <i class="fas fa-key"></i>
                                </button>
                            </form>

                            {{-- Supprimer --}}
                            @if(!$estDernierAdmin)
                            <form method="POST" action="{{ route('utilisateurs.destroy', $utilisateur) }}" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="action-btn delete" title="Supprimer"
                                    onclick="return confirm('Supprimer {{ $utilisateur->prenom }} {{ $utilisateur->nom }} ?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @else
                            <span title="Dernier administrateur national — non supprimable" style="font-size: 12px; color: #9CA3AF; font-style: italic;">
                                <i class="fas fa-shield-alt me-1"></i>Dernier admin
                            </span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div style="text-align: center; padding: 60px 20px; color: #9CA3AF;">
                            <i class="fas fa-users" style="font-size: 48px; opacity: 0.3; display: block; margin-bottom: 16px;"></i>
                            <p class="mb-2 fw-semibold">Aucun utilisateur trouvé</p>
                            <a href="{{ route('utilisateurs.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i>Ajouter un utilisateur
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($utilisateurs->hasPages())
    <div style="padding: 16px 24px; border-top: 1px solid #F3F4F6;">
        {{ $utilisateurs->links() }}
    </div>
    @endif
</div>

{{-- Modal Credentials --}}
@if(session('nouveau_utilisateur'))
@php $creds = session('nouveau_utilisateur'); @endphp
<div id="modalCredentials" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 9999; display: flex; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 20px; width: 100%; max-width: 460px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.3); margin: 20px;">
        <div style="background: linear-gradient(135deg, #1E3A8A, #3B82F6); padding: 28px; text-align: center;">
            <div style="width: 64px; height: 64px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                <i class="fas fa-user-check" style="font-size: 28px; color: white;"></i>
            </div>
            <h4 style="color: white; margin: 0; font-weight: 700;">Compte Créé !</h4>
            <p style="color: rgba(255,255,255,0.8); margin: 6px 0 0; font-size: 14px;">{{ $creds['nom_complet'] }}</p>
        </div>
        <div style="padding: 24px;">
            <p style="font-size: 14px; color: #374151; margin-bottom: 16px; font-weight: 600;">
                <i class="fas fa-key me-2" style="color: #3B82F6;"></i>Identifiants de connexion :
            </p>
            <div style="background: #F9FAFB; border-radius: 10px; padding: 14px 16px; margin-bottom: 10px; border: 1px solid #E5E7EB;">
                <div style="font-size: 11px; color: #9CA3AF; text-transform: uppercase; margin-bottom: 4px;">Email</div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 14px; font-weight: 700; color: #1F2937;" id="credEmail">{{ $creds['email'] }}</span>
                    <button onclick="copier('credEmail', this)" style="background: #EFF6FF; color: #2563EB; border: none; border-radius: 6px; font-size: 12px; padding: 5px 10px; cursor: pointer;">
                        <i class="fas fa-copy me-1"></i>Copier
                    </button>
                </div>
            </div>
            <div style="background: #1E3A8A; border-radius: 10px; padding: 14px 16px; margin-bottom: 16px;">
                <div style="font-size: 11px; color: rgba(255,255,255,0.6); text-transform: uppercase; margin-bottom: 4px;">Mot de passe temporaire</div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 20px; font-weight: 700; color: white; font-family: monospace; letter-spacing: 2px;" id="credPassword">{{ $creds['mot_de_passe'] }}</span>
                    <button onclick="copier('credPassword', this)" style="background: rgba(255,255,255,0.2); color: white; border: none; border-radius: 6px; font-size: 12px; padding: 5px 10px; cursor: pointer;">
                        <i class="fas fa-copy me-1"></i>Copier
                    </button>
                </div>
            </div>
            @if(!empty($creds['email_envoye']))
            <div style="background: #D1FAE5; border: 1px solid #6EE7B7; border-radius: 10px; padding: 12px 16px; margin-bottom: 12px; font-size: 13px; color: #065F46;">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Email envoyé</strong> — Les identifiants ont été transmis à <strong>{{ $creds['email'] }}</strong>.
            </div>
            @else
            <div style="background: #FEE2E2; border: 1px solid #FCA5A5; border-radius: 10px; padding: 12px 16px; margin-bottom: 12px; font-size: 13px; color: #991B1B;">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Email non envoyé</strong> — Notez ce mot de passe et transmettez-le manuellement.
            </div>
            @endif
            <div style="background: #FFFBEB; border: 1px solid #FCD34D; border-radius: 10px; padding: 12px 16px; margin-bottom: 20px;">
                <p style="color: #92400E; font-size: 12px; margin: 0;">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    <strong>Rôle :</strong> {{ ucfirst(str_replace('_', ' ', $creds['role'])) }} —
                    Le mot de passe devra être changé à la première connexion.
                </p>
            </div>
            <button onclick="fermerModal()" style="background: linear-gradient(135deg, #1E3A8A, #3B82F6); color: white; border: none; border-radius: 10px; padding: 14px; width: 100%; font-size: 15px; font-weight: 600; cursor: pointer;">
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