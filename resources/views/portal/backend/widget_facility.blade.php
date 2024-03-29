@extends('portal.backend.layouts.master', ['title' => 'Widget'])
@section('jsplugin')
    <script src="{{asset('assets/js/plugins/datatables/datatables.min.js')}}"></script>
    <script src="{{asset('assets/js/plugins/selects/select2.min.js')}}"></script>
    <script src="{{asset('assets/js/plugins/notifications/pnotify.min.js')}}"></script>
@endsection
@section('jsscript')
    <script src="{{asset('assets/apps/portal/backend/js/widget_facility.js')}}"></script>
@endsection
@section('breadcrumb')
    <span class="breadcrumb-item">Widget</span>
    <span class="breadcrumb-item active">Fasilitas</span>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-white header-elements-inline">
                    <h6 class="card-title font-weight-semibold">DATA FASILITAS</h6>
                    <div class="header-elements">
                        <button type="button" class="btn btn-primary btn-labeled btn-labeled-left font-weight-semibold" data-toggle="modal" data-target="#modal-facility"><b><i class="icon-office"></i> </b>TAMBAH</button>
                    </div>
                </div>
                <table class="table datatable-facility table-bordered">
                    <thead>
                    <tr>
                        <th>NO</th>
                        <th>NAMA FASILITAS</th>
                        <th>DISKRIPSI</th>
                        <th>GAMBAR</th>
                        <th>AKSI</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('modal')
    <div id="modal-facility" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-white">
                    <h5 class="modal-title font-weight-semibold title">Tambah Fasilitas</h5>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="facility_id">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-12">
                                <label>Nama Fasilitas :</label>
                                <input type="text" id="facility_name" placeholder="Kelas Ber-AC" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-12">
                                <label>Diskripsi :</label>
                                <textarea id="facility_desc" placeholder="Kelas Ber-AC" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-12">
                                <label>Tautan :</label>
                                <input type="text" id="facility_link" placeholder="https://example.sch.id/artikel/3/baca" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-12">
                                <label>Gambar : </label>
                                <input type="file" id="facility_image" class="form-control-uniform-custom">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="submit" value="store" class="btn btn-sm bg-primary btn-labeled btn-labeled-left"><b><i class="icon-floppy-disk"></i></b>SIMPAN</button>
                    <button type="button" class="btn btn-sm bg-danger btn-labeled btn-labeled-left" data-dismiss="modal"><b><i class="icon-close2"></i> </b>KELUAR</button>
                </div>
            </div>
        </div>
    </div>
@endsection
