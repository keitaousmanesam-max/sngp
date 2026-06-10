<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ordonnance extends Model
{
    use HasFactory;

    protected $fillable = [
        'vente_id',
        'medecin_nom',
        'medecin_prenom',
        'date_prescription',
        'numero_ordonnance',
        'etablissement_soin',
        'patient_reference',
        'scan_ordonnance',
        'observations',
    ];

    protected $casts = [
        'date_prescription' => 'date',
    ];

    // Relations
    public function vente()
    {
        return $this->belongsTo(Vente::class);
    }

    // Accesseur : nom complet du médecin
    public function getNomCompletMedecinAttribute(): string
    {
        return $this->medecin_prenom . ' ' . $this->medecin_nom;
    }
}