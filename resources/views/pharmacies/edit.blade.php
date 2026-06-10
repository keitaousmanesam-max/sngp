@extends('layouts.app')

@section('title', 'Modifier Pharmacie')

@push('styles')
<style>
    .form-card {
        background: white;
        border-radius: 16px;
        padding: 32px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        border: 1px solid #f0f0f0;
    }
    .form-section-title {
        font-size: 15px;
        font-weight: 600;
        color: #1B4F8A;
        padding-bottom: 12px;
        border-bottom: 2px solid #EFF6FF;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .form-label { font-weight: 600; font-size: 13px; color: #374151; margin-bottom: 6px; }
    .form-control:focus, .form-select:focus {
        border-color: #2E75B6;
        box-shadow: 0 0 0 3px rgba(46,117,182,0.15);
    }
    .required-star { color: #EF4444; margin-left: 2px; }
</style>
@endpush

@section('content')

<!-- Header -->
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('pharmacies.show', $pharmacie) }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-edit me-2"></i>Modifier la Pharmacie</h1>
        <p class="text-muted mb-0">{{ $pharmacie->nom }} — {{ $pharmacie->numero_agrement }}</p>
    </div>
</div>

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-4">
    <i class="fas fa-times-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form method="POST" action="{{ route('pharmacies.update', $pharmacie) }}">
    @csrf @method('PUT')
    <div class="row g-4">

        <!-- Colonne principale -->
        <div class="col-12 col-lg-8">

            <!-- Informations générales -->
            <div class="form-card mb-4">
                <div class="form-section-title">
                    <i class="fas fa-hospital"></i> Informations Générales
                </div>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Nom de la Pharmacie <span class="required-star">*</span></label>
                        <input type="text" name="nom"
                            class="form-control @error('nom') is-invalid @enderror"
                            value="{{ old('nom', $pharmacie->nom) }}">
                        @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Numéro d'Agrément <span class="required-star">*</span></label>
                        <input type="text" name="numero_agrement"
                            class="form-control @error('numero_agrement') is-invalid @enderror"
                            value="{{ old('numero_agrement', $pharmacie->numero_agrement) }}">
                        @error('numero_agrement')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Date d'Agrément <span class="required-star">*</span></label>
                        <input type="date" name="date_agrement"
                            class="form-control @error('date_agrement') is-invalid @enderror"
                            value="{{ old('date_agrement', $pharmacie->date_agrement->format('Y-m-d')) }}">
                        @error('date_agrement')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <!-- Localisation -->
            <div class="form-card mb-4">
                <div class="form-section-title">
                    <i class="fas fa-map-marker-alt"></i> Localisation
                </div>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Région <span class="required-star">*</span></label>
                        <select name="region" class="form-select @error('region') is-invalid @enderror">
                            <option value="">Sélectionner une région</option>
                            @foreach(['Conakry', 'Boké', 'Kindia', 'Mamou', 'Labé', 'Faranah', 'Kankan', 'N\'Zérékoré'] as $region)
                            <option value="{{ $region }}" {{ old('region', $pharmacie->region) == $region ? 'selected' : '' }}>
                                {{ $region }}
                            </option>
                            @endforeach
                        </select>
                        @error('region')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Préfecture <span class="required-star">*</span></label>
                        <input type="text" name="prefecture"
                            class="form-control @error('prefecture') is-invalid @enderror"
                            value="{{ old('prefecture', $pharmacie->prefecture) }}">
                        @error('prefecture')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Commune</label>
                        <input type="text" name="commune"
                            class="form-control @error('commune') is-invalid @enderror"
                            value="{{ old('commune', $pharmacie->commune) }}">
                        @error('commune')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Adresse Complète <span class="required-star">*</span></label>
                        <input type="text" name="adresse"
                            class="form-control @error('adresse') is-invalid @enderror"
                            value="{{ old('adresse', $pharmacie->adresse) }}">
                        @error('adresse')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                            value="{{ old('telephone', $pharmacie->telephone) }}">
                        @error('telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Email <span class="required-star">*</span></label>
                        <input type="email" name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $pharmacie->email) }}">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <!-- Observations -->
            <div class="form-card">
                <div class="form-section-title">
                    <i class="fas fa-sticky-note"></i> Observations
                </div>
                <textarea name="observations" class="form-control" rows="4"
                    placeholder="Observations ou remarques supplémentaires...">{{ old('observations', $pharmacie->observations) }}</textarea>
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
                    <span class="badge rounded-pill px-4 py-2 fs-6
                        {{ $pharmacie->statut == 'active' ? 'bg-success' : ($pharmacie->statut == 'suspendue' ? 'bg-warning text-dark' : 'bg-danger') }}">
                        @if($pharmacie->statut == 'active')
                            <i class="fas fa-check-circle me-1"></i>Active
                        @elseif($pharmacie->statut == 'suspendue')
                            <i class="fas fa-pause-circle me-1"></i>Suspendue
                        @else
                            <i class="fas fa-times-circle me-1"></i>Fermée
                        @endif
                    </span>
                    <p class="text-muted mt-3 mb-0" style="font-size: 13px;">
                        Enregistrée le {{ $pharmacie->created_at->format('d/m/Y') }}
                    </p>
                </div>
            </div>

            <!-- Boutons -->
            <div class="form-card">
                <button type="submit" class="btn btn-primary w-100 mb-3 py-2">
                    <i class="fas fa-save me-2"></i>Enregistrer les Modifications
                </button>
                <a href="{{ route('pharmacies.show', $pharmacie) }}" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-times me-2"></i>Annuler
                </a>
            </div>

        </div>
    </div>
</form>

@endsection