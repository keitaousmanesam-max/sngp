@extends('layouts.app')

@section('title', 'Catégories Thérapeutiques')

@push('styles')
<style>
    .stats-card { background: white; border-radius: 12px; padding: 20px 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; border-left: 4px solid transparent; }
    .stats-card.total { border-left-color: #3B82F6; }
    .stats-card.actif { border-left-color: #10B981; }
    .stats-value { font-size: 32px; font-weight: 700; line-height: 1; }
    .stats-label { font-size: 13px; color: #6B7280; margin-top: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
    .table-card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; overflow: hidden; }
    .table-card .table { margin-bottom: 0; }
    .table-card thead { background: #F9FAFB; }
    .table-card thead th { border: none; color: #6B7280; font-weight: 600; font-size: 12px; text-transform: uppercase; padding: 14px 20px; }
    .table-card tbody td { padding: 14px 20px; vertical-align: middle; border-color: #F3F4F6; font-size: 14px; }
    .table-card tbody tr:hover { background: #F9FAFB; }
    .action-btn { width: 30px; height: 30px; border-radius: 7px; border: none; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; transition: all 0.2s; cursor: pointer; }
    .action-btn:hover { opacity: 0.8; transform: scale(1.1); }
    .action-btn.edit { background: #EFF6FF; color: #2563EB; }
    .action-btn.toggle { background: #FEF3C7; color: #D97706; }
    .action-btn.delete { background: #FEF2F2; color: #DC2626; }
    .modal-form-card { background: white; border-radius: 16px; }
</style>
@endpush

@section('content')

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-tags me-2"></i>Catégories Thérapeutiques</h1>
        <p class="text-muted mb-0">Gestion des familles thérapeutiques des médicaments</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreate">
        <i class="fas fa-plus me-2"></i>Nouvelle Catégorie
    </button>
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
            <div class="stats-label"><i class="fas fa-tags me-1"></i>Total</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stats-card actif">
            <div class="stats-value" style="color: #10B981;">{{ $stats['actives'] }}</div>
            <div class="stats-label"><i class="fas fa-check-circle me-1"></i>Actives</div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div style="background: white; border-radius: 12px; padding: 20px 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; margin-bottom: 24px;">
    <form method="GET" action="{{ route('categories.index') }}">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-5">
                <label class="form-label fw-semibold small">Rechercher</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Nom ou code..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label fw-semibold small">Statut</label>
                <select name="statut" class="form-select">
                    <option value="">Tous</option>
                    <option value="actif" {{ request('statut') == 'actif' ? 'selected' : '' }}>Actif</option>
                    <option value="inactif" {{ request('statut') == 'inactif' ? 'selected' : '' }}>Inactif</option>
                </select>
            </div>
            <div class="col-6 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="fas fa-filter me-1"></i>Filtrer</button>
                <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
            </div>
        </div>
    </form>
</div>

<!-- Tableau -->
<div class="table-card">
    <div style="padding: 20px 24px; border-bottom: 1px solid #F3F4F6;">
        <h6 class="mb-0 fw-semibold" style="color: #374151;">{{ $categories->total() }} catégorie(s)</h6>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Catégorie</th>
                    <th>Code</th>
                    <th>Description</th>
                    <th>Produits</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $categorie)
                <tr>
                    <td class="fw-semibold" style="color: #1F2937;">{{ $categorie->nom }}</td>
                    <td>
                        <span style="background: #EFF6FF; color: #1E40AF; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-family: monospace; font-weight: 700;">
                            {{ $categorie->code }}
                        </span>
                    </td>
                    <td style="color: #6B7280;">{{ $categorie->description ?? '—' }}</td>
                    <td>
                        <span class="fw-semibold">{{ $categorie->produits_count }}</span>
                        <small class="text-muted"> produit(s)</small>
                    </td>
                    <td>
                        @if($categorie->actif)
                            <span style="background: #D1FAE5; color: #065F46; padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 600;">
                                <i class="fas fa-check-circle me-1"></i>Actif
                            </span>
                        @else
                            <span style="background: #FEF3C7; color: #92400E; padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 600;">
                                <i class="fas fa-pause-circle me-1"></i>Inactif
                            </span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="action-btn edit" title="Modifier"
                                onclick="editCategorie({{ $categorie->id }}, '{{ $categorie->nom }}', '{{ $categorie->code }}', '{{ $categorie->description }}')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" action="{{ route('categories.toggle', $categorie) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <button class="action-btn toggle" title="{{ $categorie->actif ? 'Désactiver' : 'Activer' }}">
                                    <i class="fas fa-{{ $categorie->actif ? 'pause' : 'play' }}"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('categories.destroy', $categorie) }}" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="action-btn delete" title="Supprimer"
                                    onclick="return confirm('Supprimer cette catégorie ?')">
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
                            <i class="fas fa-tags" style="font-size: 48px; opacity: 0.3; display: block; margin-bottom: 16px;"></i>
                            <p class="mb-2 fw-semibold">Aucune catégorie trouvée</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($categories->hasPages())
    <div style="padding: 16px 24px; border-top: 1px solid #F3F4F6;">
        {{ $categories->links() }}
    </div>
    @endif
</div>

<!-- Modal Créer -->
<div class="modal fade" id="modalCreate" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <div style="background: linear-gradient(135deg, #1E3A8A, #3B82F6); padding: 24px; border-radius: 16px 16px 0 0;">
                <h5 style="color: white; margin: 0; font-weight: 700;"><i class="fas fa-plus me-2"></i>Nouvelle Catégorie</h5>
            </div>
            <form method="POST" action="{{ route('categories.store') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nom <span style="color: #EF4444;">*</span></label>
                        <input type="text" name="nom" class="form-control" placeholder="Ex: Antibiotiques" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Code <span style="color: #EF4444;">*</span></label>
                        <input type="text" name="code" class="form-control" placeholder="Ex: ATB" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Description de la catégorie..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Modifier -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <div style="background: linear-gradient(135deg, #059669, #10B981); padding: 24px; border-radius: 16px 16px 0 0;">
                <h5 style="color: white; margin: 0; font-weight: 700;"><i class="fas fa-edit me-2"></i>Modifier la Catégorie</h5>
            </div>
            <form method="POST" id="formEdit" action="">
                @csrf @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nom <span style="color: #EF4444;">*</span></label>
                        <input type="text" name="nom" id="editNom" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Code <span style="color: #EF4444;">*</span></label>
                        <input type="text" name="code" id="editCode" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" id="editDescription" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save me-2"></i>Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function editCategorie(id, nom, code, description) {
        document.getElementById('editNom').value = nom;
        document.getElementById('editCode').value = code;
        document.getElementById('editDescription').value = description || '';
        document.getElementById('formEdit').action = '/categories/' + id;
        new bootstrap.Modal(document.getElementById('modalEdit')).show();
    }
</script>
@endpush