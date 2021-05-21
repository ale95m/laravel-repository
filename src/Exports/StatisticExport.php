<?php

namespace Easy\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StatisticExport implements FromCollection, ShouldAutoSize, WithStyles
{
    use Exportable;

    protected $items;
    protected $encrypt;
    protected $custom_styles = [
        2 => ['font' => ['bold' => true]],
    ];

    public function __construct($items, $encrypt = false)
    {
        $this->items = $items;
        $this->encrypt = $encrypt;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->items;
    }

    public function styles(Worksheet $sheet)
    {
        return $this->custom_styles;
    }
}
