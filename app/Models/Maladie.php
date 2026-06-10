<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Maladie extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'code_cim10',
        'description',
        'categorie',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    // Relations
    public function produits()
    {
        return $this->belongsToMany(Produit::class, 'produit_maladie');
    }

    // Scopes
    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }
}