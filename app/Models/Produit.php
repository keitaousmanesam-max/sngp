<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Produit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'dci',
        'nom_commercial',
        'code_barre',
        'code_produit',
        'categorie_id',
        'forme_galenique',
        'dosage',
        'unite',
        'necessite_ordonnance',
        'prix_vente_recommande',
        'statut',
        'description',
    ];

    protected $casts = [
        'necessite_ordonnance'   => 'boolean',
        'prix_vente_recommande'  => 'decimal:2',
    ];

    // Relations
    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }

    public function maladies()
    {
        return $this->belongsToMany(Maladie::class, 'produit_maladie');
    }

    public function lots()
    {
        return $this->hasMany(Lot::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function lignesVente()
    {
        return $this->hasMany(LigneVente::class);
    }

    public function lignesCommande()
    {
        return $this->hasMany(LigneCommande::class);
    }

    // Scopes
    public function scopeActif($query)
    {
        return $query->where('statut', 'actif');
    }

    public function scopeAvecOrdonnance($query)
    {
        return $query->where('necessite_ordonnance', true);
    }

    public function scopeSansOrdonnance($query)
    {
        return $query->where('necessite_ordonnance', false);
    }
}