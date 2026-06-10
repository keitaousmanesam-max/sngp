<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LigneCommande extends Model
{
    use HasFactory;

    protected $table = 'lignes_commande';

    protected $fillable = [
        'commande_id',
        'produit_id',
        'quantite_commandee',
        'quantite_recue',
        'prix_unitaire',
        'montant_total',
        'statut',
        'motif_rejet',
    ];

    protected $casts = [
        'quantite_commandee' => 'integer',
        'quantite_recue'     => 'integer',
        'prix_unitaire'      => 'decimal:2',
        'montant_total'      => 'decimal:2',
    ];

    // Relations
    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    // Scopes
    public function scopeValide($query)
    {
        return $query->where('statut', 'valide');
    }

    public function scopeRejete($query)
    {
        return $query->where('statut', 'rejete');
    }
}