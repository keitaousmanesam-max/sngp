@extends('layouts.app')

@section('title', 'Modifier Lot')

@push('styles')
<style>
    .form-card { background: white; border-radius: 16px; padding: 32px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; }
    .form-section-title { font-size: 15px; font-weight: 600; color: #7C3AED; padding-bottom: 12px; border-bottom: 2px solid #EDE9FE; margin-bottom: 24px; display: flex; align-items: center; gap: 8px; }
    .form-label { font-weight: 600; font-size: 13px; color: #374151; margin-bottom: 6px; }
    .form-control:focus, .form-select:focus { border-color: #8B5CF6; box-shadow: 0 0 0 3px rgba(139,92,246,0.15); }
    .required-star { color: #EF4444; margin-left: 2px; }
    .info-readonly { background: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 8px; padding: 10px 14px; font-size: 14px; color: #374151; font-weight: 600; }
</style>
@endpush

@section('content')

<!-- Header -->
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('lots.show', $lot) }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-edit me-2"></i>Modifier le Lot</h1>
        <p class="text-muted mb-0">{{ $lot->numero_lot }} — {{ $lot->produit->dci }}</p>
    </div>
</div>

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-4">
    <i class="fas fa-times-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form method="POST" action="{{ route('lots.update', $lot) }}">
    @csrf @method('PUT')
    <div class="row g-4">

        <!-- Colonne principale -->
        <div class="col-12 col-lg-8">

            <!-- Informations non modifiables -->
            <div class="form-card mb-4">
                <div class="form-section-title">
                    <i class="fas fa-lock"></i> Informations Non Modifiables
                </div>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Produit</label>
                        <div class="info-readonly">
                            <i class="fas fa-pills me-2 text-muted"></i>{{ $lot->produit->dci }}
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Numéro de Lot</label>
                        <div class="info-readonly" style="font-family: monospace;">
                            {{ $lot->numero_lot }}
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Quantité Reçue</label>
                        <div class="info-readonly">{{ number_format($lot->quantite_recue) }} unités</div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Quantité Disponible</label>
                        <div class="info-readonly" style="color: {{ $lot->quantite_disponible <= 10 ? '#EF4444' : '#065F46' }}">
                            {{ number_format($lot->quantite_disponible) }} unités
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations modifiables -->
            <div class="form-card">
                <div class="form-section-title">
                    <i class="fas fa-edit"></i> Informations Modifiables
                </div>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Date d'Expiration <span class="required-star">*</span></label>
                        <input type="date" name="date_expiration"
                            class="form-control @error('date_expiration') is-invalid @enderror"
                            value="{{ old('date_expiration', $lot->date_expiration->format('Y-m-d')) }}">
                        @error('date_expiration')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Statut <span class="required-star">*</span></label>
                        <select name="statut" class="form-select @error('statut') is-invalid @enderror">
                            <option value="disponible" {{ old('statut', $lot->statut) == 'disponible' ? 'selected' : '' }}>Disponible</option>
                            <option value="epuise" {{ old('statut', $lot->statut) == 'epuise' ? 'selected' : '' }}>Épuisé</option>
                            <option value="expire" {{ old('statut', $lot->statut) == 'expire' ? 'selected' : '' }}>Expiré</option>
                            <option value="retire" {{ old('statut', $lot->statut) == 'retire' ? 'selected' : '' }}>Retiré du marché</option>
                        </select>
                        @error('statut')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Prix d'Achat Unitaire (GNF) <span class="required-star">*</span></label>
                        <input type="number" name="prix_achat_unitaire"
                            class="form-control @error('prix_achat_unitaire') is-invalid @enderror"
                            value="{{ old('prix_achat_unitaire', $lot->prix_achat_unitaire) }}" min="0">
                        @error('prix_achat_unitaire')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

        </div>

        <!-- Colonne latérale -->
        <div class="col-12 col-lg-4">

            <!-- Résumé -->
            <div class="form-card mb-4">
                <div class="form-section-title">
                    <i class="fas fa-chart-pie"></i> Résumé
                </div>
                @php $joursRestants = now()->diffInDays($lot->date_expiration, false); @endphp
                <div style="text-align: center; padding: 16px 0;">
                    <div style="font-size: 36px; font-weight: 700; color: {{ $joursRestants < 0 ? '#EF4444' : ($joursRestants <= 30 ? '#F59E0B' : '#10B981') }}">
                        {{ abs($joursRestants) }}j
                    </div>
                    <div style="font-size: 13px; color: #6B7280;">
                        {{ $joursRestants < 0 ? 'Expiré depuis' : 'Jours avant expiration' }}
                    </div>
                    <hr>
                    <div style="font-size: 13px; color: #374151;">
                        Valeur stock : <strong>{{ number_format($lot->quantite_disponible * $lot->prix_achat_unitaire, 0, ',', ' ') }} GNF</strong>
                    </div>
                </div>
            </div>

            <!-- Boutons -->
            <div class="form-card">
                <button type="submit" class="btn btn-primary w-100 mb-3 py-2">
                    <i class="fas fa-save me-2"></i>Enregistrer les Modifications
                </button>
                <a href="{{ route('lots.show', $lot) }}" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-times me-2"></i>Annuler
                </a>
            </div>

        </div>
    </div>
</form>

@endsection