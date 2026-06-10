<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'pharmacie_id',
        'action',
        'module',
        'model_type',
        'model_id',
        'donnees_avant',
        'donnees_apres',
        'ip_address',
        'user_agent',
        'description',
    ];

    protected $casts = [
        'donnees_avant' => 'array',
        'donnees_apres' => 'array',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pharmacie()
    {
        return $this->belongsTo(Pharmacie::class);
    }

    // Scopes
    public function scopeParModule($query, $module)
    {
        return $query->where('module', $module);
    }

    public function scopeParUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeParPharmacie($query, $pharmacieId)
    {
        return $query->where('pharmacie_id', $pharmacieId);
    }
}