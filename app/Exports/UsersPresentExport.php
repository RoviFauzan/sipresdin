<?php

namespace App\Exports;

use App\Present;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Fromview;

class UsersPresentExport implements Fromview
{
    private $tanggal;

    public function __construct($tanggal) {
        $this->tanggal = $tanggal;
    }

    public function view(): View
    {
        // Query dengan join tabel users untuk mengambil NRP
        $presents = Present::whereTanggal($this->tanggal)
            ->join('users', 'presents.user_id', '=', 'users.id') // Join dengan tabel users
            ->orderBy('users.nrp', 'asc') // Urutkan berdasarkan NRP
            ->orderBy('presents.jam_masuk', 'asc') // Urutkan berdasarkan jam masuk
            ->select('presents.*', 'users.nrp') // Ambil kolom dari tabel presents dan NRP dari users
            ->get();

        return view('presents.users-excel', compact('presents'));
    }
}
