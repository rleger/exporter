<?php

namespace App\Exports;

use App\Exports\Sheets\AllEntriesSheet;
use App\Exports\Sheets\UniqueEntriesSheet;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EntriesMultiSheetExport implements WithMultipleSheets
{
    public function __construct(
        protected Collection $entries,
        protected bool $includeUserColumn = false
    ) {
    }

    /**
     * @return array<int, \Maatwebsite\Excel\Concerns\FromCollection>
     */
    public function sheets(): array
    {
        return [
            new UniqueEntriesSheet($this->entries),
            new AllEntriesSheet($this->entries, $this->includeUserColumn),
        ];
    }
}
