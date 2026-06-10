<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'pharmacie_id',
        'produit_id',
        'lot_id',
        'quantite_disponible',
        'seuil_alerte',
    ];

    protected $casts = [
        'quantite_disponible' => 'integer',
        'seuil_alerte'        => 'integer',
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

    // Scopes
    public function scopeCritique($query)
    {
        return $query->whereColumn('quantite_disponible', '<=', 'seuil_alerte');
    }

    public function scopeRupture($query)
    {
        return $query->where('quantite_disponible', 0);
    }

    // Vérifier si le stock est critique
    public function estCritique(): bool
    {
        return $this->quantite_disponible <= $this->seuil_alerte;
    }

    // Vérifier si le stock est en rupture
    public function estEnRupture(): bool
    {
        return $this->quantite_disponible === 0;
    }
}