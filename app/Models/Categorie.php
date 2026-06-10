<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Categorie extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'code',
        'description',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    // Relations
    public function produits()
    {
        return $this->hasMany(Produit::class);
    }

    // Scopes
    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }
}