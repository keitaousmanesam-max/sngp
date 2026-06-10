<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LigneRetour extends Model
{
    use HasFactory;

    protected $table = 'ligne_retours';

    protected $fillable = [
        'retour_id',
        'produit_id',
        'quantite',
        'prix_unitaire',
        'sous_total',
        'motif_ligne',
    ];

    public function retour()
    {
        return $this->belongsTo(Retour::class);
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }
}