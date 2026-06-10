<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Retour extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'numero_retour',
        'pharmacie_id',
        'vente_id',
        'produit_id',
        'lot_id',
        'demande_par',
        'valide_par',
        'quantite',
        'montant_rembourse',
        'motif',
        'motif_detail',
        'statut',
        'destination',
        'valide_le',
        'motif_rejet',
    ];

    protected $casts = [
        'quantite'          => 'integer',
        'montant_rembourse' => 'decimal:2',
        'valide_le'         => 'datetime',
    ];

    // Relations
    public function pharmacie()
    {
        return $this->belongsTo(Pharmacie::class);
    }

    public function vente()
    {
        return $this->belongsTo(Vente::class);
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    public function lot()
    {
        return $this->belongsTo(Lot::class);
    }

    public function demandePar()
    {
        return $this->belongsTo(User::class, 'demande_par');
    }

    public function validePar()
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    // Scopes
    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    public function scopeValide($query)
    {
        return $query->where('statut', 'valide');
    }

    public function scopeParPharmacie($query, $pharmacieId)
    {
        return $query->where('pharmacie_id', $pharmacieId);
    }
    public function lignes()
    
    {
        return $this->hasMany(LigneRetour::class);
    }
}