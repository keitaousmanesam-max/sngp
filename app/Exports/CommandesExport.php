<?php

namespace App\Exports;

use App\Models\Commande;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CommandesExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $dateDebut, $dateFin, $user;

    public function __construct($dateDebut, $dateFin, $user)
    {
        $this->dateDebut = $dateDebut;
        $this->dateFin   = $dateFin;
        $this->user      = $user;
    }

    public function collection()
    {
        $isNational  = $this->user->hasRole('admin_national');
        $pharmacieId = $this->user->pharmacie_id;

        return Commande::with(['fournisseur', 'pharmacie'])
            ->whereBetween(DB::raw('DATE(created_at)'), [$this->dateDebut, $this->dateFin])
            ->when(!$isNational, fn($q) => $q->where('pharmacie_id', $pharmacieId))
            ->orderBy('created_at', 'desc')->get()
            ->map(fn($c) => [
                'N° Commande'    => $c->numero_commande,
                'Date'           => $c->created_at->format('d/m/Y'),
                'Pharmacie'      => $c->pharmacie->nom ?? '—',
                'Fournisseur'    => $c->fournisseur->nom ?? '—',
                'Montant (GNF)'  => $c->montant_total,
                'Statut'         => ucfirst($c->statut),
            ]);
    }

    public function headings(): array
    {
        return ['N° Commande', 'Date', 'Pharmacie', 'Fournisseur', 'Montant (GNF)', 'Statut'];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1E3A8A']]]];
    }

    public function title(): string { return 'Commandes'; }
}