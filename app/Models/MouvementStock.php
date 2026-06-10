<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MouvementStock extends Model
{
    use HasFactory;

    protected $table = 'mouvements_stock';

    protected $fillable = [
        'pharmacie_id',
        'produit_id',
        'lot_id',
        'user_id',
        'type',
        'quantite',
        'quantite_avant',
        'quantite_apres',
        'motif',
        'reference',
    ];

    protected $casts = [
        'quantite'       => 'integer',
        'quantite_avant' => 'integer',
        'quantite_apres' => 'integer',
    ];

    // Relations
    public function pharmacie()
    {
        return $this->belongsTo(Pharmacie::class);
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    public function lot()
    {
        return $this->belongsTo(Lot::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeEntrees($query)
    {
        return $query->where('type', 'entree');
    }

    public function scopeSorties($query)
    {
        return $query->where('type', 'sortie');
    }

    public function scopeParPharmacie($query, $pharmacieId)
    {
        return $query->where('pharmacie_id', $pharmacieId);
    }
}