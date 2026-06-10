@extends('layouts.app')

@section('title', 'Modifier Fournisseur')

@push('styles')
<style>
    .form-card { background: white; border-radius: 16px; padding: 32px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; }
    .form-section-title { font-size: 15px; font-weight: 600; color: #059669; padding-bottom: 12px; border-bottom: 2px solid #ECFDF5; margin-bottom: 24px; display: flex; align-items: center; gap: 8px; }
    .form-label { font-weight: 600; font-size: 13px; color: #374151; margin-bottom: 6px; }
    .form-control:focus, .form-select:focus { border-color: #10B981; box-shadow: 0 0 0 3px rgba(16,185,129,0.15); }
    .required-star { color: #EF4444; margin-left: 2px; }
</style>
@endpush

@section('content')

<!-- Header -->
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('fournisseurs.show', $fournisseur) }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-edit me-2"></i>Modifier le Fournisseur</h1>
        <p class="text-muted mb-0">{{ $fournisseur->nom }}</p>
    </div>
</div>

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-4">
    <i class="fas fa-times-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form method="POST" action="{{ route('fournisseurs.update', $fournisseur) }}">
    @csrf @method('PUT')
    <div class="row g-4">

        <!-- Colonne principale -->
        <div class="col-12 col-lg-8">

            <!-- Informations générales -->
            <div class="form-card mb-4">
                <div class="form-section-title">
                    <i class="fas fa-truck"></i> Informations Générales
                </div>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Nom du Fournisseur <span class="required-star">*</span></label>
                        <input type="text" name="nom"
                            class="form-control @error('nom') is-invalid @enderror"
                            value="{{ old('nom', $fournisseur->nom) }}">
                        @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Numéro de Registre</label>
                        <input type="text" name="numero_registre"
                            class="form-control @error('numero_registre') is-invalid @enderror"
                            value="{{ old('numero_registre', $fournisseur->numero_registre) }}">
                        @error('numero_registre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Pays</label>
                        <select name="pays" class="form-select @error('pays') is-invalid @enderror">
                            @foreach(['Guinée', 'Sénégal', 'Mali', 'Côte d\'Ivoire', 'France', 'Maroc', 'Autre'] as $pays)
                            <option value="{{ $pays }}" {{ old('pays', $fournisseur->pays) == $pays ? 'selected' : '' }}>
                                {{ $pays }}
                            </option>
                            @endforeach
                        </select>
                        @error('pays')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <!-- Contact -->
            <div class="form-card mb-4">
                <div class="form-section-title">
                    <i class="fas fa-phone"></i> Contact
                </div>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Téléphone <span class="required-star">*</span></label>
                        <input type="text" name="telephone"
                            class="form-control @error('telephone') is-invalid @enderror"
                            value="{{ old('telephone', $fournisseur->telephone) }}">
                        @error('telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Email <span class="required-star">*</span></label>
                        <input type="email" name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $fournisseur->email) }}">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Ville</label>
                        <input type="text" name="ville"
                            class="form-control @error('ville') is-invalid @enderror"
                            value="{{ old('ville', $fournisseur->ville) }}">
                        @error('ville')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Adresse <span class="required-star">*</span></label>
                        <input type="text" name="adresse"
                            class="form-control @error('adresse') is-invalid @enderror"
                            value="{{ old('adresse', $fournisseur->adresse) }}">
                        @error('adresse')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <!-- Observations -->
            <div class="form-card">
                <div class="form-section-title">
                    <i class="fas fa-sticky-note"></i> Observations
                </div>
                <textarea name="observations" class="form-control" rows="4">{{ old('observations', $fournisseur->observations) }}</textarea>
            </div>

        </div>

        <!-- Colonne latérale -->
        <div class="col-12 col-lg-4">

            <!-- Statut actuel -->
            <div class="form-card mb-4">
                <div class="form-section-title">
                    <i class="fas fa-info-circle"></i> Statut Actuel
                </div>
                <div class="text-center py-2">
                    @php
                        $badgeClass = match($fournisseur->statut) {
                            'valide'     => 'bg-success',
                            'en_attente' => 'bg-warning text-dark',
                            'suspendu'   => 'bg-danger',
                            default      => 'bg-secondary'
                        };
                        $badgeLabel = match($fournisseur->statut) {
                            'valide'     => 'Validé',
                            'en_attente' => 'En attente',
                            'suspendu'   => 'Suspendu',
                            default      => 'Rejeté'
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }} rounded-pill px-4 py-2 fs-6">
                        {{ $badgeLabel }}
                    </span>
                    <p class="text-muted mt-3 mb-0" style="font-size: 13px;">
                        Enregistré le {{ $fournisseur->created_at->format('d/m/Y') }}
                    </p>
                </div>
            </div>

            <!-- Boutons -->
            <div class="form-card">
                <button type="submit" class="btn btn-success w-100 mb-3 py-2">
                    <i class="fas fa-save me-2"></i>Enregistrer les Modifications
                </button>
                <a href="{{ route('fournisseurs.show', $fournisseur) }}" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-times me-2"></i>Annuler
                </a>
            </div>

        </div>
    </div>
</form>

@endsection