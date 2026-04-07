<table>
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Keterangan</th>
            <th>Jam Masuk</th>
            <th>Jam Keluar</th>
            <th>Total Jam</th>
        </tr>
    </thead>
    <tbody>
        @php
            $total = 0;
        @endphp
        @foreach ($presents as $present)
            <tr>
                <td>{{ date('d/m/Y', strtotime($present->tanggal)) }}</td>
                <td>{{ $present->keterangan }}</td>
                @if ($present->jam_masuk)
                    <td>{{ date('H:i:s', strtotime($present->jam_masuk)) }}</td>
                @else
                    <td>-</td>
                @endif
                @if ($present->jam_keluar)
                    <td>{{ date('H:i:s', strtotime($present->jam_keluar)) }}</td>
                    <td>
                        @if (strtotime($present->jam_keluar) <= strtotime($present->jam_masuk))
                            {{ \Carbon\Carbon::parse($present->jam_masuk)->diffInHours(\Carbon\Carbon::parse($present->jam_keluar)) }}
                        @else
                            @if (strtotime($present->jam_keluar) >= strtotime(config('absensi.jam_pulang') . ' +2 hours'))
                                {{ \Carbon\Carbon::parse($present->jam_masuk)->diffInHours(\Carbon\Carbon::parse($present->jam_keluar)) }}
                            @else
                                {{ \Carbon\Carbon::parse($present->jam_masuk)->diffInHours(\Carbon\Carbon::parse($present->jam_keluar)) }}
                            @endif
                        @endif
                    </td>
                @else
                    <td>-</td>
                    <td>-</td>
                @endif
                @php
                    $total += (\Carbon\Carbon::parse($present->jam_masuk)->diffInHours(\Carbon\Carbon::parse($present->jam_keluar)));
                @endphp
            </tr>
        @endforeach
        <tr>
            <td colspan="5"><b>Total Telat {{ $totalJamTelat }} Jam Bulan Ini</b></td>
        </tr>
        <tr>
            <td colspan="5"><b>Total Kerja {{ $total }} Jam Bulan Ini</b></td>
        </tr>
    </tbody>
</table>