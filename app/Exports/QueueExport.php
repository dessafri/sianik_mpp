<?php

namespace App\Exports;

use App\Models\Queue;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class QueueExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Nomor Antrian',
            'Nama',
            'Telepon',
            'NIK',
            'Tanggal',
            'Jenis Antrian',
        ];
    }

    public function map($row): array
    {
        $token = $row->letter . '-' . $row->number;
        return [
            'Nomor Antrian' => $token,
            'Nama' => $row->name,
            'Telepon' => $row->phone,
            'NIK' => $row->nik,
            'Tanggal' => $row->created_at,
            'Jenis Antrian' => $row->status_queue,
        ];
    }
}
