<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Commande extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'numero_commande',
        'pharmacie_id',
        'fournisseur_id',
        'created_by',
        'valide_par',
        'statut',
        'montant_total',
        'date_commande',
        'date_livraison_prevue',
        'date_livraison_reelle',
        'date_reception',
        'bon_livraison',
        'observations',
    ];

    protected $casts = [
        'montant_total'         => 'decimal:2',
        'date_commande'         => 'date',
        'date_livraison_prevue' => 'date',
        'date_livraison_reelle' => 'date',
        'date_reception'        => 'date',
    ];

    // Relations
    public function pharmacie()
    {
        return $this->belongsTo(Pharmacie::class);
    }

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function creePar()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validePar()
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    public function lignes()
    {
        return $this->hasMany(LigneCommande::class);
    }

    public function lots()
    {
        return $this->hasMany(Lot::class);
    }

    // Scopes
    public function scopeBrouillon($query)
    {
        return $query->where('statut', 'brouillon');
    }

    public function scopeEnCours($query)
    {
        return $query->whereNotIn('statut', ['finalisee', 'annulee']);
    }

    public function scopeParPharmacie($query, $pharmacieId)
    {
        return $query->where('pharmacie_id', $pharmacieId);
    }

    // Vérifier si la commande est modifiable
    public function estModifiable(): bool
    {
        return $this->statut === 'brouillon';
    }

    // Vérifier si la commande est annulable
    public function estAnnulable(): bool
    {
        return in_array($this->statut, ['brouillon', 'envoyee']);
    }
}