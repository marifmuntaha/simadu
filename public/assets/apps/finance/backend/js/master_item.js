var itemjs = function () {
    var csrf_token = {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
    var _componetnDataTable = function () {
        $('.datatable-item').DataTable({
            autoWidth: false,
            bLengthChange: true,
            bSort: false,
            scrollX: true,
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
                {className: 'text-center', targets: 3}
            ],
            ajax: ({
                headers: csrf_token,
                url: baseurl + '/master/item',
                type: 'post',
                dataType: 'json',
                data: {
                    '_type' : 'data',
                    '_data' : 'all'
                }
            })
        }).on('click', '.btn-edit', function (e) {
            e.preventDefault();
            var item_id = $(this).data('num');
            $.ajax({
                headers: csrf_token,
                url : baseurl + '/master/item',
                type: 'post',
                dataType: 'json',
                data: {
                    '_type': 'data',
                    '_data': 'item',
                    'item_id': item_id,
                },
                success : function (resp) {
                    $('.title-add').html('UBAH DATA');
                    $('#submit').val('update');
                    $('#item_id').val(resp.item_id)
                    $('#item_code').val(resp.item_code);
                    $('#item_name').val(resp.item_name);
                }
            });
        }).on('click', '.btn-delete', function (e) {
            e.preventDefault();
            var item_id = $(this).data('num');
            $.ajax({
                headers: csrf_token,
                url : baseurl + '/master/item',
                type: 'post',
                dataType: 'json',
                data: {
                    '_type': 'delete',
                    'item_id': item_id,
                },
                success : function (resp) {
                    new PNotify({
                        title: resp['title'],
                        text: resp['text'],
                        addclass: 'alert bg-'+resp['class']+' border-'+resp['class']+' alert-styled-left'
                    });
                    $('.datatable-item').DataTable().ajax.reload();
                }
            });
        })
    }

    var _componentSelect = function () {
        $('.dataTables_length select').select2({
            minimumResultsForSearch: Infinity,
            dropdownAutoWidth: true,
            width: 'auto'
        });
        $('.select').select2({
            minimumResultsForSearch: Infinity
        });
    }

    var _componentSubmit = function () {
        $("#submit").click(function () {
            $.ajax({
                headers: csrf_token,
                url : baseurl + '/master/item',
                type: 'post',
                dataType: 'json',
                data: {
                    '_type': $('#submit').val(),
                    'item_id': $('#item_id').val(),
                    'item_code': $('#item_code').val(),
                    'item_name': $('#item_name').val(),
                },
                success : function (resp) {
                    new PNotify({
                        title: resp['title'],
                        text: resp['text'],
                        addclass: 'alert bg-'+resp['class']+' border-'+resp['class']+' alert-styled-left'
                    });
                    $('.title-add').html('TAMBAH DATA');
                    $('#submit').val('store');
                    $('#item_id').val('')
                    $('#item_code').val('')
                    $('#item_name').val('')
                    $('.datatable-item').DataTable().ajax.reload();
                }
            })
        })
    }

    return {
        init: function() {
            _componetnDataTable();
            _componentSelect();
            _componentSubmit();
        }
    }
}();

document.addEventListener('DOMContentLoaded', function() {
    itemjs.init();
});
