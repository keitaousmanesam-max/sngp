<?php

namespace App\Exports;

use App\Models\Lot;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StocksExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function collection()
    {
        $isNational = $this->user->hasRole('admin_national');
        $pharmacieId = $this->user->pharmacie_id;

        $lots = Lot::with(['produit', 'pharmacie'])
            ->when(!$isNational, fn($q) => $q->where('pharmacie_id', $pharmacieId))
            ->orderBy('date_expiration', 'asc')
            ->get();

        return $lots->map(function($lot) {
            return [
                'Produit'          => $lot->produit->dci ?? '—',
                'Nom Commercial'   => $lot->produit->nom_commercial ?? '—',
                'N° Lot'           => $lot->numero_lot,
                'Pharmacie'        => $lot->pharmacie->nom ?? '—',
                'Qté Disponible'   => $lot->quantite_disponible,
                'Prix Achat (GNF)' => $lot->prix_achat_unitaire,
                'Valeur (GNF)'     => $lot->quantite_disponible * $lot->prix_achat_unitaire,
                'Date Expiration'  => $lot->date_expiration->format('d/m/Y'),
                'Statut'           => $lot->statut,
            ];
        });
    }

    public function headings(): array
    {
        return ['Produit', 'Nom Commercial', 'N° Lot', 'Pharmacie', 'Qté Disponible', 'Prix Achat (GNF)', 'Valeur (GNF)', 'Date Expiration', 'Statut'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1E3A8A']]],
        ];
    }

    public function title(): string
    {
        return 'Stocks ' . now()->format('d/m/Y');
    }
}