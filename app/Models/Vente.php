<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vente extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'numero_vente',
        'pharmacie_id',
        'user_id',
        'valide_par',
        'type_vente',
        'avec_ordonnance',
        'montant_total',
        'montant_paye',
        'monnaie_rendue',
        'mode_paiement',
        'statut',
        'motif_annulation',
        'annulee_le',
        'annulee_par',
    ];

    protected $casts = [
        'montant_total'  => 'decimal:2',
        'montant_paye'   => 'decimal:2',
        'monnaie_rendue' => 'decimal:2',
        'annulee_le'     => 'datetime',
    ];

    // Relations
    public function pharmacie()
    {
        return $this->belongsTo(Pharmacie::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function validePar()
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    public function annuleePar()
    {
        return $this->belongsTo(User::class, 'annulee_par');
    }

    public function lignes()
    {
        return $this->hasMany(LigneVente::class);
    }

    public function ordonnance()
    {
        return $this->hasOne(Ordonnance::class);
    }

    public function retours()
    {
        return $this->hasMany(Retour::class);
    }

    // Scopes
    public function scopeCompletee($query)
    {
        return $query->where('statut', 'completee');
    }

    public function scopeAvecOrdonnance($query)
    {
        return $query->where('type_vente', 'avec_ordonnance');
    }

    public function scopeParPharmacie($query, $pharmacieId)
    {
        return $query->where('pharmacie_id', $pharmacieId);
    }

    public function scopeAujourdhui($query)
    {
        return $query->whereDate('created_at', today());
    }

    // Vérifier si la vente est annulable
    public function estAnnulable(): bool
    {
        return $this->statut === 'completee';
    }
}