<?php

namespace App\Exports;

use App\Models\Vente;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VentesExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $dateDebut;
    protected $dateFin;
    protected $user;

    public function __construct($dateDebut, $dateFin, $user)
    {
        $this->dateDebut = $dateDebut;
        $this->dateFin   = $dateFin;
        $this->user      = $user;
    }

    public function collection()
    {
        $isNational = $this->user->hasRole('admin_national');
        $pharmacieId = $this->user->pharmacie_id;

        $ventes = Vente::with(['user', 'pharmacie'])
            ->whereBetween(DB::raw('DATE(created_at)'), [$this->dateDebut, $this->dateFin])
            ->where('statut', 'completee')
            ->when(!$isNational, fn($q) => $q->where('pharmacie_id', $pharmacieId))
            ->orderBy('created_at', 'desc')
            ->get();

        return $ventes->map(function($vente) {
            return [
                'N° Vente'      => $vente->numero_vente,
                'Date'          => $vente->created_at->format('d/m/Y H:i'),
                'Pharmacie'     => $vente->pharmacie->nom ?? '—',
                'Montant (GNF)' => $vente->montant_total,
                'Payé (GNF)'    => $vente->montant_paye,
                'Vendeur'       => ($vente->user->prenom ?? '') . ' ' . ($vente->user->nom ?? ''),
                'Type'          => $vente->type_vente ?? '—',
            ];
        });
    }

    public function headings(): array
    {
        return ['N° Vente', 'Date', 'Pharmacie', 'Montant (GNF)', 'Payé (GNF)', 'Vendeur', 'Type'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1E3A8A']]],
        ];
    }

    public function title(): string
    {
        return 'Ventes ' . $this->dateDebut . ' au ' . $this->dateFin;
    }
}