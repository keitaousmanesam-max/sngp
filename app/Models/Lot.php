<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lot extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'numero_lot',
        'produit_id',
        'fournisseur_id',
        'pharmacie_id',
        'commande_id',
        'date_fabrication',
        'date_expiration',
        'quantite_recue',
        'quantite_disponible',
        'prix_achat_unitaire',
        'date_reception',
        'statut',
        'observations',
    ];

    protected $casts = [
        'date_fabrication'    => 'date',
        'date_expiration'     => 'date',
        'date_reception'      => 'date',
        'prix_achat_unitaire' => 'decimal:2',
    ];

    // Relations
    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function pharmacie()
    {
        return $this->belongsTo(Pharmacie::class);
    }

    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function mouvements()
    {
        return $this->hasMany(MouvementStock::class);
    }

    public function lignesVente()
    {
        return $this->hasMany(LigneVente::class);
    }

    public function retours()
    {
        return $this->hasMany(Retour::class);
    }

    // Scopes
    public function scopeDisponible($query)
    {
        return $query->where('statut', 'disponible');
    }

    public function scopeExpire($query)
    {
        return $query->where('statut', 'expire');
    }

    public function scopeExpirationProche($query, $jours = 30)
    {
        return $query->where('statut', 'disponible')
                     ->whereDate('date_expiration', '<=', now()->addDays($jours))
                     ->whereDate('date_expiration', '>=', now());
    }

    // FEFO : trier par date d'expiration croissante
    public function scopeFefo($query)
    {
        return $query->orderBy('date_expiration', 'asc');
    }

    // Vérifier si le lot est expiré
    public function estExpire(): bool
    {
        return $this->date_expiration->isPast();
    }
}