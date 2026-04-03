@extends('layouts.admin.app')

@section('title', 'Intelligent Repeat Suggestions')

@section('breadcrumb-items')
    <li class="breadcrumb-item">Lead Management</li>
    <li class="breadcrumb-item active">Repeat Suggestions</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5>Relationship Re-engagement Pipeline</h5>
                        <span>These customers finished their jobs over 10 days ago. It's the perfect time to check if they need a repeat order!</span>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fa fa-search"></i></span>
                            <input type="text" id="realtime_search" class="form-control" placeholder="Search customer, job #..." value="{{ request('search') }}">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="table_container">
                        @include('lead.leads.repeat_suggestions_table')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    function markRepeat(id) {
        if(confirm('Are you sure you want to mark this customer for a new repeat lead record?')) {
            window.location.href = "{{ route('lead.create') }}?repeat_lead_id=" + id;
        }
    }

    $(document).ready(function() {
        let currentPage = 1;

        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                const context = this;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }

        function refreshTable(page = currentPage) {
            currentPage = page;
            var $container = $('#table_container');
            $container.css('opacity', '0.5');
            
            $.ajax({
                url: '{{ route("lead.repeat_suggestions") }}',
                data: { 
                    page: page, 
                    search: $('#realtime_search').val()
                },
                type: 'GET',
                success: function(data) {
                    $container.html(data).css('opacity', '1');
                }
            });
        }

        $('#realtime_search').on('keyup search', debounce(function() { refreshTable(1); }, 500));
        $(document).on('click', '.pages a', function(e) { 
            e.preventDefault(); 
            let url = $(this).attr('href');
            if(url) {
                let page = url.split("page=")[1];
                refreshTable(page);
            }
        });
    });
</script>
@endsection
