<?php

namespace App\Http\Controllers;

use App\Present;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $present = Present::whereUserId(auth()->user()->id)->whereTanggal(date('Y-m-d'))->first();

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

        return view('home', compact('present','libur','holiday'));
    }
}
