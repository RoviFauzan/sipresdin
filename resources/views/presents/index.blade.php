@extends('layouts.app')

@section('title')
Kehadiran - {{ config('app.name') }}
@endsection

@section('header')
    <div class="row">
        <div class="col-xl-3 col-lg-6">
            <div class="card card-stats mb-4 mb-xl-0">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h5 class="card-title text-uppercase text-muted mb-0">Masuk</h5>
                            <span class="h2 font-weight-bold mb-0">{{ $masuk }}</span>
                        </div>
                        <div class="col-auto">
                            <div class="icon icon-shape bg-gradient-green text-white rounded-circle shadow">
                                <i class="fas fa-check"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="card card-stats mb-4 mb-xl-0">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h5 class="card-title text-uppercase text-muted mb-0">Telat</h5>
                            <span class="h2 font-weight-bold mb-0">{{ $telat }}</span>
                        </div>
                        <div class="col-auto">
                            <div class="icon icon-shape bg-gradient-yellow text-white rounded-circle shadow">
                            <i class="fas fa-business-time"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="card card-stats mb-4 mb-xl-0">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h5 class="card-title text-uppercase text-muted mb-0">Izin</h5>
                            <span class="h2 font-weight-bold mb-0">{{ $izin }}</span>
                        </div>
                        <div class="col-auto">
                            <div class="icon icon-shape bg-gradient-blue text-white rounded-circle shadow">
                                <i class="fas fa-user-clock"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6">
            <div class="card card-stats mb-4 mb-xl-0">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h5 class="card-title text-uppercase text-muted mb-0">Alpha</h5>
                            <span class="h2 font-weight-bold mb-0">{{ $alpha }}</span>
                        </div>
                        <div class="col-auto">
                            <div class="icon icon-shape bg-danger text-white rounded-circle shadow">
                                <i class="fas fa-times"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')

<!-- Begin Page Content -->
    <div class="container">
        <div class="card shadow h-100">
            <div class="card-header">
                <h5 class="m-0 pt-1 font-weight-bold float-left">Kehadiran</h5>
                <form class="float-right" action="{{ route('kehadiran.excel-users') }}" method="get">
                    <input type="hidden" name="tanggal" value="{{ request('tanggal', date('Y-m-d')) }}">
                    <button class="btn btn-sm btn-primary" type="submit" title="Download"><i class="fas fa-download"></i></button>
                </form>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 mb-1">
                        <form action="{{ route('kehadiran.search') }}" method="get">
                            <div class="form-group row">
                                <label for="tanggal" class="col-form-label col-sm-3">Tanggal</label>
                                <div class="input-group col-sm-9">
                                    <input type="date" class="form-control" name="tanggal" id="tanggal" value="{{ request('tanggal', date('Y-m-d')) }}">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-primary" type="submit">Cari</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-lg-6">
                        <div class="float-right">
                            {{ $presents->links() }}
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>NRP</th>
                                <th>Nama</th>
                                <th>Keterangan</th>
                                <th>Jam Masuk</th>
                                <th>Jam Keluar</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($presents as $present)
                                <tr>
                                    <th>{{ $rank++ }}</th>
                                    <td><a href="{{ route('users.show', $present->user) }}">{{ $present->user->nrp }}</a></td>
                                    <td>{{ $present->user->nama }}</td>
                                    <td>
                                        <a href="#" data-toggle="modal" data-target="#kehadiranModal" data-id="{{ $present->id }}" data-keterangan="{{ $present->keterangan }}" data-jam_masuk="{{ $present->jam_masuk }}" data-jam_keluar="{{ $present->jam_keluar }}">
                                            {{ $present->keterangan }}
                                        </a>
                                    </td>
                                    <td>{{ $present->jam_masuk ? date('H:i:s', strtotime($present->jam_masuk)) : '-' }}</td>
                                    <td>{{ $present->jam_keluar ? date('H:i:s', strtotime($present->jam_keluar)) : '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for updating attendance -->
    <div class="modal fade" id="kehadiranModal" tabindex="-1" role="dialog" aria-labelledby="kehadiranLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="kehadiranLabel">Ubah Kehadiran</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formUbahKehadiran" action="" method="post">
                    @csrf @method('patch')
                    <div class="modal-body">
                        <div class="form-group row">
                            <label for="ubah_keterangan" class="col-form-label col-sm-3">Keterangan</label>
                            <div class="col-sm-9">
                                <select class="form-control" name="keterangan" id="ubah_keterangan">
                                    <option value="Alpha">Alpha</option>
                                    <option value="Masuk">Masuk</option>
                                    <option value="Telat">Telat</option>
                                    <option value="Sakit">Sakit</option>
                                    <option value="Izin">Izin</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row" id="jamMasuk">
                            <label for="ubah_jam_masuk" class="col-form-label col-sm-3">Jam Masuk</label>
                            <div class="col-sm-9">
                                <input type="time" name="jam_masuk" id="ubah_jam_masuk" class="form-control">
                            </div>
                        </div>
                        <div class="form-group row" id="jamKeluar">
                            <label for="ubah_jam_keluar" class="col-form-label col-sm-3">Jam Keluar</label>
                            <div class="col-sm-9">
                                <input type="time" name="jam_keluar" id="ubah_jam_keluar" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Toggle jamMasuk and jamKeluar fields based on keterangan
            $('#ubah_keterangan').on('change', function () {
                if (this.value === 'Masuk') {
                    $('#jamMasuk, #jamKeluar').show();
                    $('#ubah_jam_masuk').val('08:00'); // Set default value to 08:00
                } else if (this.value === 'Telat') {
                    $('#jamMasuk, #jamKeluar').show();
                } else {
                    $('#jamMasuk, #jamKeluar').hide();
                    $('#ubah_jam_masuk').val(''); // Clear value
                    $('#ubah_jam_keluar').val(''); // Clear value
                }
            });

            // Fill modal fields with data
            $('#kehadiranModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var id = button.data('id');
                var keterangan = button.data('keterangan');
                var jamMasuk = button.data('jam_masuk');
                var jamKeluar = button.data('jam_keluar');

                $('#formUbahKehadiran').attr('action', '/kehadiran/' + id);
                $('#ubah_keterangan').val(keterangan).change();
                $('#ubah_jam_masuk').val(jamMasuk);
                $('#ubah_jam_keluar').val(jamKeluar);
            });
        });
    </script>
@endpush