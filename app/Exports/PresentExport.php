<?php

namespace App\Exports;

use App\Present;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class PresentExport implements FromView, WithEvents
{
    private $user_id, $bulan;

    public function __construct($user_id, $bulan) {
        $this->user_id = $user_id;
        $this->bulan = $bulan;
    }

    public function view(): View
    {
        $data = explode('-', $this->bulan);
        $presents = Present::whereUserId($this->user_id)
            ->whereMonth('tanggal', $data[1])
            ->whereYear('tanggal', $data[0])
            ->join('users', 'presents.user_id', '=', 'users.id')
            ->where('users.nrp', 'not like', '1%')
            ->orderBy('users.nrp', 'asc')
            ->select('presents.*', 'users.nrp')
            ->get();

        $kehadiran = Present::whereUserId($this->user_id)
            ->whereMonth('tanggal', $data[1])
            ->whereYear('tanggal', $data[0])
            ->whereKeterangan('telat')
            ->join('users', 'presents.user_id', '=', 'users.id')
            ->where('users.nrp', 'not like', '1%')
            ->orderBy('users.nrp', 'asc')
            ->select('presents.*', 'users.nrp')
            ->get();

        $totalJamTelat = 0;
        foreach ($kehadiran as $present) {
            $totalJamTelat += \Carbon\Carbon::parse($present->jam_masuk)
                ->diffInHours(\Carbon\Carbon::parse(config('absensi.jam_masuk')));
        }

        return view('presents.excel-user', compact('presents', 'totalJamTelat'));
    }

    // Metode untuk menambahkan event AfterSheet
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Data untuk menghitung panjang isi kolom
                $presents = Present::whereUserId($this->user_id)
                    ->whereMonth('tanggal', explode('-', $this->bulan)[1])
                    ->whereYear('tanggal', explode('-', $this->bulan)[0])
                    ->join('users', 'presents.user_id', '=', 'users.id')
                    ->where('users.nrp', 'not like', '1%')
                    ->orderBy('users.nrp', 'asc')
                    ->select('presents.*', 'users.nrp', 'users.nama')
                    ->get();
    
                $margin = 8; // Margin lebih besar untuk memberikan ruang
                $minWidth = 15; // Lebar minimum untuk kolom agar tetap terlihat
    
                if ($presents->isEmpty()) {
                    // Jika data kosong, gunakan lebar default untuk semua kolom
                    $event->sheet->getColumnDimension('A')->setWidth($minWidth); // NRP
                    $event->sheet->getColumnDimension('B')->setWidth($minWidth); // Nama
                    $event->sheet->getColumnDimension('C')->setWidth($minWidth); // Keterangan
                    $event->sheet->getColumnDimension('D')->setWidth(20); // Jam Masuk
                    $event->sheet->getColumnDimension('E')->setWidth(20); // Jam Keluar
                    $event->sheet->getColumnDimension('F')->setWidth(20); // Total Jam
                } else {
                    // Jika data tidak kosong, hitung lebar kolom berdasarkan data
                    $maxNrpLength = max(array_map(function($present) {
                        return isset($present['nrp']) ? strlen($present['nrp']) : 0;
                    }, $presents->toArray()));
    
                    $maxNameLength = max(array_map(function($present) {
                        return isset($present['nama']) ? strlen($present['nama']) : 0;
                    }, $presents->toArray()));
    
                    $maxKeteranganLength = max(array_map(function($present) {
                        return isset($present['keterangan']) ? strlen($present['keterangan']) : 0;
                    }, $presents->toArray()));
    
                    // Mengatur lebar kolom dengan mempertimbangkan margin dan lebar minimum
                    $event->sheet->getColumnDimension('A')->setWidth(max($maxNrpLength + $margin, $minWidth)); // NRP
                    $event->sheet->getColumnDimension('B')->setWidth(max($maxNameLength + $margin, $minWidth)); // Nama
                    $event->sheet->getColumnDimension('C')->setWidth(max($maxKeteranganLength + $margin, $minWidth)); // Keterangan
                    $event->sheet->getColumnDimension('D')->setWidth(20); // Jam Masuk
                    $event->sheet->getColumnDimension('E')->setWidth(20); // Jam Keluar
                    $event->sheet->getColumnDimension('F')->setWidth(20); // Total Jam
                }
            },
        ];
    }
    
}
