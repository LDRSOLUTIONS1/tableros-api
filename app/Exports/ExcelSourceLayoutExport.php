<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ExcelSourceLayoutExport implements
    FromArray,
    WithHeadings,
    ShouldAutoSize,
    WithTitle,
    WithStyles
{
    public function __construct(
        private array $headings
    ) {}

    public function array(): array
    {
        // Una fila vacía para que el usuario comience a capturar.
        return [
            array_fill(0, count($this->headings), null)
        ];
    }


    public function headings(): array
    {
        return $this->headings;
    }

    public function title(): string
    {
        return 'Carga';
    }

    public function styles(Worksheet $sheet): array
    {
        $lastColumn = Coordinate::stringFromColumnIndex(
            count($this->headings)
        );

        $sheet->freezePane('A2');

        $sheet->setAutoFilter(
            "A1:{$lastColumn}1"
        );

        $sheet->getRowDimension(1)->setRowHeight(30);

        $sheet->getDefaultColumnDimension()->setWidth(18);

        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => [
                        'rgb' => 'FFFFFF'
                    ],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => '17365D'
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ],
        ];
    }
}
