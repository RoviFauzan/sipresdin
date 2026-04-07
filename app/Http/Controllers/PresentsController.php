<?php

namespace App\Http\Controllers;

use App\Present;
use App\User;
use App\Exports\PresentExport;
use App\Exports\UsersPresentExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class PresentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $presents = Present::whereTanggal(date('Y-m-d'))
            ->join('users', 'presents.user_id', '=', 'users.id')
            ->where('users.nrp', 'not like', '1%') // Kecualikan NRP yang diawali dengan 1
            ->orderBy('users.nrp', 'asc') // Urutkan berdasarkan NRP
            ->select('presents.*') // Pilih hanya kolom yang diperlukan
            ->paginate(10);
    
        $masuk = Present::whereTanggal(date('Y-m-d'))
            ->whereKeterangan('masuk')
            ->join('users', 'presents.user_id', '=', 'users.id')
            ->where('users.nrp', 'not like', '1%')
            ->count();
    
        $telat = Present::whereTanggal(date('Y-m-d'))
            ->whereKeterangan('telat')
            ->join('users', 'presents.user_id', '=', 'users.id')
            ->where('users.nrp', 'not like', '1%')
            ->count();
    
        $izin = Present::whereTanggal(date('Y-m-d'))
            ->whereKeterangan('izin')
            ->join('users', 'presents.user_id', '=', 'users.id')
            ->where('users.nrp', 'not like', '1%')
            ->count();
    
        $alpha = Present::whereTanggal(date('Y-m-d'))
            ->whereKeterangan('alpha')
            ->join('users', 'presents.user_id', '=', 'users.id')
            ->where('users.nrp', 'not like', '1%')
            ->count();
    
        $rank = $presents->firstItem();
    
        return view('presents.index', compact('presents', 'rank', 'masuk', 'telat', 'izin', 'alpha'));
    }
    

    public function search(Request $request)
    {
        $tanggal = $request->tanggal ?? date('Y-m-d');
        
        $presents = Present::whereTanggal($tanggal)->orderBy('jam_masuk', 'desc')->paginate(10)->appends(['tanggal' => $tanggal]);
        $masuk = Present::whereTanggal($request->tanggal)->whereKeterangan('masuk')->count();
        $telat = Present::whereTanggal($request->tanggal)->whereKeterangan('telat')->count();
        $izin = Present::whereTanggal($request->tanggal)->whereKeterangan('izin')->count();
        $alpha = Present::whereTanggal($request->tanggal)->whereKeterangan('alpha')->count();
        $rank = $presents->firstItem();
        return view('presents.index', compact('presents','rank','masuk','telat','izin','alpha'));
    }

    public function cari(Request $request, User $user)
    {
        $request->validate([
            'bulan' => ['required']
        ]);
        $data = explode('-',$request->bulan);
        $presents = Present::whereUserId($user->id)->whereMonth('tanggal',$data[1])->whereYear('tanggal',$data[0])->orderBy('tanggal','desc')->paginate(10);
        $masuk = Present::whereUserId($user->id)->whereMonth('tanggal',$data[1])->whereYear('tanggal',$data[0])->whereKeterangan('masuk')->count();
        $telat = Present::whereUserId($user->id)->whereMonth('tanggal',$data[1])->whereYear('tanggal',$data[0])->whereKeterangan('telat')->count();
        $izin = Present::whereUserId($user->id)->whereMonth('tanggal',$data[1])->whereYear('tanggal',$data[0])->whereKeterangan('izin')->count();
        $alpha = Present::whereUserId($user->id)->whereMonth('tanggal',$data[1])->whereYear('tanggal',$data[0])->whereKeterangan('alpha')->count();
        $kehadiran = Present::whereUserId($user->id)->whereMonth('tanggal',$data[1])->whereYear('tanggal',$data[0])->whereKeterangan('telat')->get();
        $totalJamTelat = 0;
        foreach ($kehadiran as $present) {
            $totalJamTelat = $totalJamTelat + (\Carbon\Carbon::parse($present->jam_masuk)->diffInHours(\Carbon\Carbon::parse(config('absensi.jam_masuk') .' -1 hours')));
        }
        $url = 'https://api-harilibur.vercel.app/api?year=' . date('Y');
        $response = @file_get_contents($url);
        $kalender = $response ? json_decode($response, true) : [];

        $libur = false;
        $holiday = null;

        if (is_array($kalender)) {
            foreach ($kalender as $value) {
                if (($value['holiday_date'] ?? null) === date('Y-m-d')) {
                    $holiday = $value['holiday_name'] ?? 'Hari Libur';
                    $libur = true;
                    break;
                }
            }
        }

        return view('users.show', compact('presents','user','masuk','telat','izin','alpha','libur','totalJamTelat'));
    }

    public function cariDaftarHadir(Request $request)
    {
        $request->validate([
            'bulan' => ['required']
        ]);
        $data = explode('-',$request->bulan);
        $presents = Present::whereUserId(auth()->user()->id)->whereMonth('tanggal',$data[1])->whereYear('tanggal',$data[0])->orderBy('tanggal','desc')->paginate(10);
        $masuk = Present::whereUserId(auth()->user()->id)->whereMonth('tanggal',$data[1])->whereYear('tanggal',$data[0])->whereKeterangan('masuk')->count();
        $telat = Present::whereUserId(auth()->user()->id)->whereMonth('tanggal',$data[1])->whereYear('tanggal',$data[0])->whereKeterangan('telat')->count();
        $izin = Present::whereUserId(auth()->user()->id)->whereMonth('tanggal',$data[1])->whereYear('tanggal',$data[0])->whereKeterangan('izin')->count();
        $alpha = Present::whereUserId(auth()->user()->id)->whereMonth('tanggal',$data[1])->whereYear('tanggal',$data[0])->whereKeterangan('alpha')->count();
        return view('presents.show', compact('presents','masuk','telat','izin','alpha'));
    }

    public function checkIn(Request $request)
    {
        $users = User::all();
        $data['jam_masuk']  = date('H:i:s');
        $data['tanggal']    = date('Y-m-d');
        $data['user_id']    = $request->user_id;

        if (date('l') == 'Saturday' || date('l') == 'Sunday') {
            return redirect()->back()->with('error','Hari Libur Tidak bisa Check In');
        }

        foreach ($users as $user) {
            $absen = Present::whereUserId($user->id)->whereTanggal($data['tanggal'])->first();
            if (!$absen) {
                if ($user->id != $data['user_id']) {
                    Present::create([
                        'keterangan'    => 'Alpha',
                        'tanggal'       => date('Y-m-d'),
                        'user_id'       => $user->id
                    ]);
                }
            }
        }

        if (strtotime($data['jam_masuk']) >= strtotime(config('absensi.jam_masuk') .' -1 hours') && strtotime($data['jam_masuk']) <= strtotime(config('absensi.jam_masuk'))) {
            $data['keterangan'] = 'Masuk';
        } else if (strtotime($data['jam_masuk']) > strtotime(config('absensi.jam_masuk')) && strtotime($data['jam_masuk']) <= strtotime(config('absensi.jam_pulang'))) {
            $data['keterangan'] = 'Telat';
        } else {
            $data['keterangan'] = 'Alpha';
        }

        $present = Present::whereUserId($data['user_id'])->whereTanggal($data['tanggal'])->first();
        if ($present) {
            if ($present->keterangan == 'Alpha') {
                $present->update($data);
                return redirect()->back()->with('success','Check-in berhasil');
            } else {
                return redirect()->back()->with('error','Check-in gagal');
            }
        }

        Present::create($data);
        return redirect()->back()->with('success','Check-in berhasil');
    }

    public function checkOut(Request $request, Present $kehadiran)
    {
        $data['jam_keluar'] = date('H:i:s');
        $kehadiran->update($data);
        return redirect()->back()->with('success', 'Check-out berhasil');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $present = Present::whereUserId($request->user_id)->whereTanggal(date('Y-m-d'))->first();
        if ($present) {
            return redirect()->back()->with('error','Absensi hari ini telah terisi');
        }
        $data = $request->validate([
            'keterangan'    => ['required'],
            'user_id'    => ['required']
        ]);
        $data['tanggal'] = date('Y-m-d');
        if ($request->keterangan == 'Masuk' || $request->keterangan == 'Telat') {
            $data['jam_masuk'] = $request->jam_masuk;
            if (strtotime($data['jam_masuk']) >= strtotime(config('absensi.jam_masuk') .' -1 hours') && strtotime($data['jam_masuk']) <= strtotime(config('absensi.jam_masuk'))) {
                $data['keterangan'] = 'Masuk';
            } else if (strtotime($data['jam_masuk']) > strtotime(config('absensi.jam_masuk')) && strtotime($data['jam_masuk']) <= strtotime(config('absensi.jam_pulang'))) {
                $data['keterangan'] = 'Telat';
            } else {
                $data['keterangan'] = 'Alpha';
            }
        }
        Present::create($data);
        return redirect()->back()->with('success','Kehadiran berhasil ditambahkan');
    }

    public function ubah(Request $request)
    {
        $present = Present::findOrFail($request->id);
        echo json_encode($present);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $presents = Present::whereUserId(auth()->user()->id)->whereMonth('tanggal',date('m'))->whereYear('tanggal',date('Y'))->orderBy('tanggal','desc')->paginate(10);
        $masuk = Present::whereUserId(auth()->user()->id)->whereMonth('tanggal',date('m'))->whereYear('tanggal',date('Y'))->whereKeterangan('masuk')->count();
        $telat = Present::whereUserId(auth()->user()->id)->whereMonth('tanggal',date('m'))->whereYear('tanggal',date('Y'))->whereKeterangan('telat')->count();
        $izin = Present::whereUserId(auth()->user()->id)->whereMonth('tanggal',date('m'))->whereYear('tanggal',date('Y'))->whereKeterangan('izin')->count();
        $alpha = Present::whereUserId(auth()->user()->id)->whereMonth('tanggal',date('m'))->whereYear('tanggal',date('Y'))->whereKeterangan('alpha')->count();
        return view('presents.show', compact('presents','masuk','telat','izin','alpha'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Present  $kehadiran
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Present $kehadiran)
    {
        $data = $request->validate([
            'keterangan'    => ['required']
        ]);

        if ($request->jam_keluar) {
            $data['jam_keluar'] = $request->jam_keluar;
        }

        if ($request->keterangan == 'Masuk' || $request->keterangan == 'Telat') {
            $data['jam_masuk'] = $request->jam_masuk;
            if (strtotime($data['jam_masuk']) >= strtotime(config('absensi.jam_masuk') .' -1 hours') && strtotime($data['jam_masuk']) <= strtotime(config('absensi.jam_masuk'))) {
                $data['keterangan'] = 'Masuk';
            } else if (strtotime($data['jam_masuk']) > strtotime(config('absensi.jam_masuk')) && strtotime($data['jam_masuk']) <= strtotime(config('absensi.jam_pulang'))) {
                $data['keterangan'] = 'Telat';
            } else {
                $data['keterangan'] = 'Alpha';
            }
        } else {
            $data['jam_masuk'] = null;
            $data['jam_keluar'] = null;
        }
        $kehadiran->update($data);
        return redirect()->back()->with('success', 'Kehadiran tanggal "'.date('l, d F Y',strtotime($kehadiran->tanggal)).'" berhasil diubah');
    }

    public function excelUser(Request $request, User $user)
    {
        return Excel::download(new PresentExport($user->id, $request->bulan), 'kehadiran-'.$user->nrp.'-'.$request->bulan.'.xlsx');
    }

    public function excelUsers(Request $request)
    {
        return Excel::download(new UsersPresentExport($request->tanggal), 'kehadiran-'.$request->tanggal.'.xlsx');
    }
}