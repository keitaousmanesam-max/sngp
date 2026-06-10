<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Fournisseur extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nom',
        'email',
        'telephone',
        'adresse',
        'ville',
        'pays',
        'numero_registre',
        'statut',
        'observations',
        'valide_par',
        'valide_le',
    ];

    protected $casts = [
        'valide_le' => 'datetime',
    ];

    // Relations
    public function validePar()
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    public function lots()
    {
        return $this->hasMany(Lot::class);
    }

    public function commandes()
    {
        return $this->hasMany(Commande::class);
    }

    // Scopes
    public function scopeValide($query)
    {
        return $query->where('statut', 'valide');
    }

    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }
}