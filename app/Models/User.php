<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $fillable = [
        'pharmacie_id',
        'nom',
        'prenom',
        'email',
        'telephone',
        'password',
        'premiere_connexion',
        'actif',
        'derniere_connexion',
        'tentatives_connexion',
        'bloque_le',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at'    => 'datetime',
        'derniere_connexion'   => 'datetime',
        'bloque_le'            => 'datetime',
        'premiere_connexion'   => 'boolean',
        'actif'                => 'boolean',
        'password'             => 'hashed',
    ];

    // Relations
    public function pharmacie()
    {
        return $this->belongsTo(Pharmacie::class);
    }

    public function ventes()
    {
        return $this->hasMany(Vente::class);
    }

    public function commandes()
    {
        return $this->hasMany(Commande::class, 'created_by');
    }

    public function mouvementsStock()
    {
        return $this->hasMany(MouvementStock::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function retours()
    {
        return $this->hasMany(Retour::class, 'demande_par');
    }

    // Accesseur : nom complet
    public function getNomCompletAttribute(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }

    // Vérifier si le compte est bloqué
    public function estBloque(): bool
    {
        return !$this->actif || $this->bloque_le !== null;
    }

    // Vérifier si c'est la première connexion
    public function estPremiereConnexion(): bool
    {
        return $this->premiere_connexion === true;
    }

    // Scopes
    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }

    public function scopeParPharmacie($query, $pharmacieId)
    {
        return $query->where('pharmacie_id', $pharmacieId);
    }
}