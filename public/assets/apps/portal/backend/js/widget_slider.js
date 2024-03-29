var userjs = function () {

    var csrf_token = {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')};

    var _componetnDataTable = function () {
        $('.datatable-slider').DataTable({
            autoWidth: false,
            bLengthChange: true,
            bSort: false,
            scrollX: false,
            dom: '<"datatable-header"fBl><"datatable-scroll-wrap"t><"datatable-footer"ip>',
            language: {
                emptyTable: 'Tak ada data yang tersedia pada tabel ini',
                loadingRecords: '<i class="icon-spinner4 spinner"></i> Memuat data...',
                info: 'Menampilkan _START_ Sampai _END_ Total _TOTAL_ Entri',
                search: '_INPUT_',
                binfo: false,
                orderable: false,
                searchPlaceholder: 'Pencarian...',
                lengthMenu: '<span>Tampilkan:</span> _MENU_',
                paginate: { 'first': 'First', 'last': 'Last', 'next': $('html').attr('dir') === 'rtl' ? '&larr;' : '&rarr;', 'previous': $('html').attr('dir') === 'rtl' ? '&rarr;' : '&larr;' }
            },
            columnDefs : [
                {className: 'text-center', targets: 0},
                {className: 'text-center', targets: 1},
                {className: 'text-center', targets: 2},
                {className: 'text-center', targets: 3},
                {className: 'text-center', targets: 4},
                {className: 'text-center', targets: 5}
            ],
            ajax: ({
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                url: baseurl + '/widget/slider',
                type: 'post',
                dataType: 'json',
                data: {
                    '_type' : 'data',
                    '_data' : 'all'
                }
            })
        }).on('click', '.btn-edit', function (e){
            e.preventDefault();
            var slider_id = $(this).data('num');
            $.ajax({
                headers: csrf_token,
                url : baseurl + '/widget/slider',
                type: 'post',
                dataType: 'json',
                data: {
                    '_type': 'data',
                    '_data': 'slider',
                    'slider_id': slider_id,
                },
                success : function (resp) {
                    $('#slider_id').val(resp.slider_id);
                    $('#slider_title').val(resp.slider_title);
                    $('#slider_content').val(resp.slider_content);
                    $('#slider_button_link_1').val(resp.slider_button_link_1);
                    $('#slider_button_name_1').val(resp.slider_button_name_1);
                    $('#slider_button_link_2').val(resp.slider_button_link_2);
                    $('#slider_button_name_2').val(resp.slider_button_name_2);
                    $('#slider_status').val(resp.slider_status).change();
                    $('.title').html('Ubah Slider');
                    $('#submit').val('update');
                    $('#modal-slider').modal('show');

                }
            });
        }).on('click', '.btn-delete', function (e) {
            e.preventDefault();
            var slider_id = $(this).data('num');
            $.ajax({
                headers: csrf_token,
                url : baseurl + '/widget/slider',
                type: 'post',
                dataType: 'json',
                data: {
                    '_type': 'delete',
                    'slider_id': slider_id,
                },
                success : function (resp) {
                    new PNotify({
                        title: resp['title'],
                        text: resp['text'],
                        addclass: 'alert bg-'+resp['class']+' border-'+resp['class']+' alert-styled-left'
                    });
                    $('.datatable-slider').DataTable().ajax.reload();
                }
            });
        })
    }

    var _componentSubmit = function () {
        $('#submit').click(function () {
            var fd      = new FormData();
            var files   = $('#slider_image')[0].files[0];

            if (files !== undefined){

                fd.append('slider_image', files);
            }
            fd.append('_type', $('#submit').val());
            fd.append('slider_id', $('#slider_id').val());
            fd.append('slider_title', $('#slider_title').val());
            fd.append('slider_content', $('#slider_content').val());
            fd.append('slider_button_link_1', $('#slider_button_link_1').val());
            fd.append('slider_button_name_1', $('#slider_button_name_1').val());
            fd.append('slider_button_link_2', $('#slider_button_link_2').val());
            fd.append('slider_button_name_2', $('#slider_button_name_2').val());
            fd.append('slider_status', $('#slider_status').val());
            $.ajax({
                headers: csrf_token,
                url: baseurl + '/widget/slider',
                type: 'post',
                dataType: 'json',
                data: fd,
                contentType: false,
                processData: false,
                success: function (resp) {
                    new PNotify({
                        title: resp['title'],
                        text: resp['text'],
                        addclass: 'alert bg-' + resp['class'] + ' border-' + resp['class'] + ' alert-styled-left'
                    });
                    $('.datatable-slider').DataTable().ajax.reload();
                    $('#slider_id').val('');
                    $('#slider_title').val('');
                    $('#slider_content').val('');
                    $('#slider_button_link_1').val('');
                    $('#slider_button_name_1').val('');
                    $('#slider_button_link_2').val('');
                    $('#slider_button_name_2').val('');
                    $('#slider_status').val('').change();
                    $('#modal-slider').modal('hide');
                }
            });
        });
    }

    var _componentSelect2 = function() {
        $('.dataTables_length  select').select2({
            minimumResultsForSearch: Infinity,
            dropdownAutoWidth: true,
            width: 'auto'
        });
        $('.select').select2({
            minimumResultsForSearch: Infinity,
        })
    };

    var _componentFileUpload = function () {
        $('.form-control-uniform-custom').uniform({
            fileButtonHtml: 'Pilih Berkas',
            fileDefaultHtml: 'Tidak ada berkas',
            fileButtonClass: 'action btn bg-blue',
            selectClass: 'uniform-select bg-pink-400 border-pink-400'
        });
    }

    return {
        init: function() {
            _componetnDataTable();
            _componentSubmit();
            _componentSelect2();
            _componentFileUpload();
        }
    }
}();

document.addEventListener('DOMContentLoaded', function() {
    userjs.init();
});
