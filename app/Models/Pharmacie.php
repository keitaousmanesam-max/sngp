<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pharmacie extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nom',
        'numero_agrement',
        'adresse',
        'region',
        'prefecture',
        'commune',
        'telephone',
        'email',
        'date_agrement',
        'statut',
        'logo',
        'observations',
    ];

    protected $casts = [
        'date_agrement' => 'date',
    ];

    // Relations
    public function utilisateurs()
    {
        return $this->hasMany(User::class);
    }

    public function lots()
    {
        return $this->hasMany(Lot::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function commandes()
    {
        return $this->hasMany(Commande::class);
    }

    public function ventes()
    {
        return $this->hasMany(Vente::class);
    }

    public function retours()
    {
        return $this->hasMany(Retour::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }// Forcer le nom du paramètre de route

    public function getRouteKeyName(): string
    {
       return 'id';
    }

    

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('statut', 'active');
    }
}