<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LigneVente extends Model
{
    use HasFactory;

    protected $table = 'lignes_vente';

    protected $fillable = [
        'vente_id',
        'produit_id',
        'lot_id',
        'quantite',
        'prix_unitaire',
        'montant_total',
    ];

    protected $casts = [
        'quantite'      => 'integer',
        'prix_unitaire' => 'decimal:2',
        'montant_total' => 'decimal:2',
    ];

    // Relations
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
}