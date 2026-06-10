@extends('layouts.fournisseur')

@section('title', 'Mon Profil')

@push('styles')
<style>
    .profil-header {
        background: linear-gradient(135deg, #1E3A8A 0%, #3B82F6 100%);
        border-radius: 20px; padding: 32px 36px; color: white; margin-bottom: 28px;
        display: flex; align-items: center; gap: 24px; flex-wrap: wrap;
    }
    .profil-avatar-lg {
        width: 80px; height: 80px; border-radius: 20px;
        background: rgba(255,255,255,.2); border: 3px solid rgba(255,255,255,.4);
        display: flex; align-items: center; justify-content: center;
        font-size: 32px; font-weight: 800; color: white; flex-shrink: 0;
    }
    .profil-header-info h2 { font-size: 22px; font-weight: 700; margin-bottom: 4px; }
    .profil-header-info p  { font-size: 14px; opacity: .8; margin: 0; }
    .profil-badge { display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.3); border-radius: 20px; padding: 5px 14px; font-size: 12px; font-weight: 600; margin-top: 8px; }
    .profil-badge.valide { background: rgba(16,185,129,.2); border-color: rgba(16,185,129,.4); }
    .profil-badge.suspendu { background: rgba(239,68,68,.2); border-color: rgba(239,68,68,.4); }

    .info-card { background: white; border-radius: 16px; padding: 28px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; margin-bottom: 24px; }
    .card-title { font-size: 15px; font-weight: 700; color: #1E3A8A; padding-bottom: 14px; border-bottom: 2px solid #DBEAFE; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }

    .info-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px; }
    .info-field label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #93C5FD; margin-bottom: 4px; display: block; }
    .info-field .val { font-size: 15px; font-weight: 600; color: #1F2937; }
    .info-field .val.empty { color: #D1D5DB; font-style: italic; font-weight: 400; }

    .form-control:focus { border-color: #3B82F6; box-shadow: 0 0 0 3px rgba(59,130,246,.15); }
    .form-label { font-weight: 600; font-size: 13px; color: #374151; margin-bottom: 5px; }

    .stat-mini { background: #EFF6FF; border-radius: 12px; padding: 18px 20px; text-align: center; border: 1px solid #DBEAFE; }
    .stat-mini .val { font-size: 28px; font-weight: 800; color: #1E3A8A; font-family: monospace; }
    .stat-mini .lbl { font-size: 11px; color: #3B82F6; font-weight: 600; text-transform: uppercase; margin-top: 4px; }
</style>
@endpush

@section('content')

<!-- Header -->
<div class="profil-header">
    <div class="profil-avatar-lg">
        {{ strtoupper(substr($fournisseur->nom ?? 'F', 0, 2)) }}
    </div>
    <div class="profil-header-info">
        <h2><i class="fas fa-building me-2" style="font-size:18px;"></i>{{ $fournisseur->nom }}</h2>
        <p><i class="fas fa-envelope me-1"></i>{{ $fournisseur->email }}
           @if($fournisseur->telephone) &nbsp;·&nbsp; <i class="fas fa-phone me-1"></i>{{ $fournisseur->telephone }} @endif
        </p>
        <div class="profil-badge {{ $fournisseur->statut === 'valide' ? 'valide' : ($fournisseur->statut === 'suspendu' ? 'suspendu' : '') }}">
            @if($fournisseur->statut === 'valide')
                <i class="fas fa-check-circle"></i> Fournisseur Agréé SNGP
            @elseif($fournisseur->statut === 'en_attente')
                <i class="fas fa-clock"></i> En attente de validation
            @else
                <i class="fas fa-ban"></i> {{ ucfirst($fournisseur->statut) }}
            @endif
        </div>
    </div>
    <div class="ms-auto d-none d-lg-block" style="text-align:right;">
        <div style="font-size:12px;opacity:.7;margin-bottom:2px;">Membre depuis</div>
        <div style="font-size:18px;font-weight:700;">{{ $fournisseur->created_at->format('d/m/Y') }}</div>
        @if($fournisseur->valide_le)
        <div style="font-size:12px;opacity:.7;margin-top:6px;">Validé le {{ $fournisseur->valide_le->format('d/m/Y') }}</div>
        @endif
    </div>
</div>

<!-- Stats rapides -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-mini">
            <div class="val">{{ $stats['total'] }}</div>
            <div class="lbl">Commandes totales</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-mini">
            <div class="val" style="color:{{ $stats['nouvelles'] > 0 ? '#EF4444' : '#1E3A8A' }};">{{ $stats['nouvelles'] }}</div>
            <div class="lbl">Nouvelles</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-mini">
            <div class="val">{{ $stats['finalisees'] }}</div>
            <div class="lbl">Finalisées</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-mini">
            <div class="val" style="font-size:18px;">{{ number_format($stats['ca_total'], 0, ',', ' ') }}</div>
            <div class="lbl">CA Total (GNF)</div>
        </div>
    </div>
</div>

<div class="row g-4">

    <!-- Infos affichées -->
    <div class="col-12 col-lg-5">
        <div class="info-card">
            <div class="card-title"><i class="fas fa-id-card"></i>Informations de l'entreprise</div>
            <div class="info-grid">
                <div class="info-field">
                    <label>Nom / Raison sociale</label>
                    <div class="val">{{ $fournisseur->nom }}</div>
                </div>
                <div class="info-field">
                    <label>Registre de commerce</label>
                    <div class="val {{ !$fournisseur->numero_registre ? 'empty' : '' }}">{{ $fournisseur->numero_registre ?? 'Non renseigné' }}</div>
                </div>
                <div class="info-field">
                    <label>Email</label>
                    <div class="val">{{ $fournisseur->email }}</div>
                </div>
                <div class="info-field">
                    <label>Téléphone</label>
                    <div class="val {{ !$fournisseur->telephone ? 'empty' : '' }}">{{ $fournisseur->telephone ?? 'Non renseigné' }}</div>
                </div>
                <div class="info-field">
                    <label>Ville</label>
                    <div class="val {{ !$fournisseur->ville ? 'empty' : '' }}">{{ $fournisseur->ville ?? 'Non renseignée' }}</div>
                </div>
                <div class="info-field">
                    <label>Pays</label>
                    <div class="val {{ !$fournisseur->pays ? 'empty' : '' }}">{{ $fournisseur->pays ?? 'Non renseigné' }}</div>
                </div>
                <div class="info-field" style="grid-column: 1 / -1;">
                    <label>Adresse</label>
                    <div class="val {{ !$fournisseur->adresse ? 'empty' : '' }}">{{ $fournisseur->adresse ?? 'Non renseignée' }}</div>
                </div>
                @if($fournisseur->observations)
                <div class="info-field" style="grid-column: 1 / -1;">
                    <label>Observations</label>
                    <div style="background:#F9FAFB; border-radius:8px; padding:10px; font-size:13px; color:#374151;">{{ $fournisseur->observations }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Formulaire modification -->
    <div class="col-12 col-lg-7">
        <div class="info-card">
            <div class="card-title"><i class="fas fa-edit"></i>Modifier mes informations de contact</div>

            @if($errors->any())
            <div class="alert alert-danger mb-4">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
            @endif

            <form method="POST" action="{{ route('fournisseur.espace.profil.update') }}">
                @csrf @method('PATCH')
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Nom / Raison sociale <span class="text-danger">*</span></label>
                        <input type="text" name="nom" class="form-control" value="{{ old('nom', $fournisseur->nom) }}" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="telephone" class="form-control" value="{{ old('telephone', $fournisseur->telephone) }}" placeholder="+224 620 000 000">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Numéro Registre de Commerce</label>
                        <input type="text" name="numero_registre" class="form-control" value="{{ old('numero_registre', $fournisseur->numero_registre) }}" placeholder="RC-GN-XXXXX">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Adresse</label>
                        <input type="text" name="adresse" class="form-control" value="{{ old('adresse', $fournisseur->adresse) }}" placeholder="Rue, quartier...">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Ville</label>
                        <input type="text" name="ville" class="form-control" value="{{ old('ville', $fournisseur->ville) }}" placeholder="Conakry">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Pays</label>
                        <input type="text" name="pays" class="form-control" value="{{ old('pays', $fournisseur->pays) }}" placeholder="Guinée">
                    </div>
                    <div class="col-12 mt-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-2"></i>Enregistrer les modifications
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Changer mot de passe -->
        <div class="info-card">
            <div class="card-title"><i class="fas fa-lock"></i>Sécurité</div>
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div style="font-size:14px;font-weight:600;color:#374151;">Mot de passe</div>
                    <div style="font-size:13px;color:#9CA3AF;">Modifiez régulièrement votre mot de passe pour sécuriser votre compte.</div>
                </div>
                <a href="{{ route('password.modifier.form') }}" class="btn btn-outline-primary btn-sm ms-3">
                    <i class="fas fa-key me-1"></i>Modifier
                </a>
            </div>
        </div>
    </div>

</div>

@endsection
