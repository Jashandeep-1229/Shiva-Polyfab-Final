<div class="modal-content border-0 shadow-lg">
    <div class="modal-header bg-dark text-white py-2 px-3">
        <div class="d-flex align-items-center">
            <div class="me-2">
                <i class="fa fa-history f-16 text-warning"></i>
            </div>
            <div>
                <h6 class="modal-title fw-bold mb-0">Stock Statement</h6>
                <div class="f-10 text-light opacity-75">{{ $color->name ?? 'N/A' }} | {{ $size->name ?? 'N/A' }}</div>
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body p-0">
        <div class="p-3 bg-light border-bottom">
            <form id="history_filter_form" class="row g-2">
                <input type="hidden" name="color_id" value="{{ $color_id }}">
                <input type="hidden" name="size_id" value="{{ $size_id }}">
                <div class="col-md-5">
                    <input type="date" name="from_date" class="form-control form-control-sm" placeholder="From Date">
                </div>
                <div class="col-md-5">
                    <input type="date" name="to_date" class="form-control form-control-sm" placeholder="To Date">
                </div>
                <div class="col-md-2">
                    <button type="button" onclick="get_history()" class="btn btn-primary btn-sm w-100"><i class="fa fa-search"></i></button>
                </div>
            </form>
        </div>
        <div id="history_container" style="max-height: 400px; overflow-y: auto;">
            <div class="loader-box"><div class="loader-37"></div></div>
        </div>
    </div>
    <div class="modal-footer bg-light border-0 py-2">
        <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Close</button>
    </div>
</div>

<script>
    $(document).ready(function() {
        get_history();
    });

    function get_history() {
        var $container = $('#history_container');
        $container.html('<div class="loader-box"><div class="loader-37"></div></div>');
        
        $.ajax({
            url: '{{ route("common_stock.history_datatable") }}',
            data: $('#history_filter_form').serialize(),
            type: 'GET',
            success: function(data) {
                $container.html(data);
                feather.replace();
            }
        });
    }

    function deleteHistory(id) {
        swal({
            title: "Are you sure?",
            text: "You won't be able to revert this stock entry!",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {
                $.get('{{ route("common_stock.delete", "") }}/' + id, function(res) {
                    if(res.result == 1) {
                        $.notify({ title: 'Deleted', message: res.message }, { type: 'success' });
                        get_history();
                        if(typeof get_datatable === 'function') get_datatable(); // For In/Out pages
                        if(typeof get_matrix === 'function') get_matrix(); // For Remaining Matrix page
                    } else {
                        $.notify({ title: 'Error', message: res.message }, { type: 'danger' });
                    }
                });
            }
        });
    }
</script>
