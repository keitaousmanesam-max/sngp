<?php

namespace App\Exports;

use App\Models\Lot;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LotsExpiresExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function collection()
    {
        $isNational  = $this->user->hasRole('admin_national');
        $pharmacieId = $this->user->pharmacie_id;

        return Lot::with(['produit', 'pharmacie'])
            ->where(function($q) {
                $q->where('date_expiration', '<', now())
                  ->orWhereBetween('date_expiration', [now(), now()->addDays(90)]);
            })
            ->where('quantite_disponible', '>', 0)
            ->when(!$isNational, fn($q) => $q->where('pharmacie_id', $pharmacieId))
            ->orderBy('date_expiration', 'asc')
            ->get()
            ->map(fn($l) => [
                'Produit'         => $l->produit->dci ?? '—',
                'N° Lot'          => $l->numero_lot,
                'Pharmacie'       => $l->pharmacie->nom ?? '—',
                'Qté Disponible'  => $l->quantite_disponible,
                'Date Expiration' => $l->date_expiration->format('d/m/Y'),
                'Jours Restants'  => (int) now()->diffInDays($l->date_expiration, false),
                'Statut'          => $l->date_expiration < now() ? 'EXPIRÉ' : 'Proche expiration',
            ]);
    }

    public function headings(): array
    {
        return ['Produit', 'N° Lot', 'Pharmacie', 'Qté Disponible', 'Date Expiration', 'Jours Restants', 'Statut'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'DC2626']],
            ],
        ];
    }

    public function title(): string
    {
        return 'Lots Expirés';
    }
}