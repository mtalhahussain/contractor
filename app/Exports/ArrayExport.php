<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;

class ArrayExport implements FromArray
{
    public function __construct(private readonly Collection $rows)
    {
    }

    public function array(): array
    {
        if ($this->rows->isEmpty()) {
            return [];
        }

        $first = (array) $this->rows->first();
        $data = [$first ? array_keys($first) : []];

        foreach ($this->rows as $row) {
            $data[] = array_values((array) $row);
        }

        return $data;
    }
}
