@extends('layouts.admin.app')

@section('title', 'Manage '.$stock_name_capital.' Stock')

@section('css')
<style>
        .select2-container .select2-selection--single{
        height:30px !important;
        padding:5px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow{
        height:12px !important;
    }
</style>
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">Manage {{$stock_name_capital}} Stock</li>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- All Client Table Start -->
        <div class="row">
            <div class="col-12">
                @if(\App\Helpers\PermissionHelper::check('stock_management', 'add'))
                <div class="card bg-light-{{(request()->in_out ?? 'in') == 'in' ? 'success' : 'danger'}}" id="add_type">
                    <form action="{{route('manage_stock.store')}}" method="POST" id="" class="modal-content" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="from"  value="Manually">
                        <input type="hidden" name="stock_name"  value="{{$stock_name}}">
                        <input type="hidden" name="unit_name"  value="{{$unit_name}}">
                        <input type="hidden" name="in_out"  value="{{$in_out}}">
                        <input type="hidden" name="average"  value="{{$average}}">
                        <div class="card-body row">
                            <div class="col-md-2">
                                <input type="date" name="date" id="date" value="{{date('Y-m-d')}}"  placeholder="Date"  class="form-control form-control-sm" required>
                            </div>
                           
                            <div class="col-md-3">
                                <select name="stock_id" id="stock_id" class="form-control form-control-sm" onchange="get_current_stock(this.value)" required>
                                    <option value="">Select {{$stock_name_capital}}</option>
                                    @foreach($stock_list as $stock)
                                        <option value="{{$stock->id}}">{{$stock->name}}</option>
                                    @endforeach
                                </select>
                                <span class="f-12 text-dark">Current Stock: <span id="current_stock">0</span></span>
                            </div>
                             <div class="col-md-3">
                                <input type="number" step="any" name="quantity" id="quantity" onkeyup="get_average(this.value)"  placeholder="Enter {{$unit_name}}"  class="form-control form-control-sm">
                                <span class="f-12 text-dark">New Stock: <span id="news_stock">0</span></span>
                            </div>
                            <div class="col-md-2">
                                <input type="number" step="any" name="average" id="average"   placeholder="Average" readonly  class="form-control form-control-sm">
                            </div>
                              
                            <div class="col-md-1">
                               <button type="submit" id="add_data" class="btn btn-{{(request()->in_out ?? 'in') == 'in' ? 'success' : 'danger'}} px-2 btn-sm w-50 ">{{($in_out == 'in') ? '+' : '-'}}</button>
                            </div>
                           
                        </div>
                    </form>
                </div>
                @endif
                <div class="card">
                    <div class="card-body">
                        <div  id="basic-2_wrapper" class="dataTables_wrapper px-2">
                            <div class="dataTables_length">
                                <label>Show 
                                    <select name="basic-2_value" onchange="get_datatable()" id="basic-2_value" aria-controls="basic-2" class="form-control form-control-sm">
                                        <option value="50">50</option>
                                        <option value="250" selected>250</option>
                                        <option value="500">500</option>
                                        <option value="1000">1000</option>
                                    </select>
                                </label>
                            </div>
                            <div class="dataTables_filter">
                                <label>Search:
                                    <input type="search" onkeyup="get_datatable()" id="basic-2_search" class="form-control form-control-sm" placeholder="Search" aria-controls="basic-2" data-bs-original-title="" title="">
                                </label>
                                
                            </div>
                        </div>
                        <div class="dt-ext" id="get_datatable">
                            <div class="loader-box"><div class="loader-37"></div></div>
                            
                        </div>
                       
                    </div>
                </div>
            </div>
        </div>
        <!-- All Client Table End -->
    </div>


    <div class="modal fade" id="edit_modal"  aria-labelledby="mySmallModalLabel" aria-hidden="true">
        <div class="modal-dialog" id="ajax_html">
            
        </div>
    </div>

    <audio id="myAudio" controls class="d-none">
        <source src="{{ asset('audio/Beep.wav') }}" type="audio/wav">
    </audio>
@endsection
@section('script')
    <script>
        $(document).ready(function(){
            get_datatable();
            $("#name").focus();
        });

        $(document).on('click','.pages a',function(n){
            n.preventDefault();
            var page = $(this).attr('href').split("page=")[1];
            get_datatable(page);
        });

      

        function get_datatable(page){
            var $container = $('#get_datatable');
            if ($container.length) {
                $container.html('<div class="loader-box"><div class="loader-37"></div></div>');
                var value = $('#basic-2_value').val();
                var search = $('#basic-2_search').val();
                var page = page ?? 1;
                $.ajax({
                    url: '{{ route("manage_stock.datatable") }}',
                    data: { page: page, value: value, search: search, _token: "{{csrf_token() }}", stock_name: "{{$stock_name}}", in_out: "{{$in_out}}" },
                    type: 'GET',
                    success: function(data){
                        $container.html(data);
                        $('#basic-test').DataTable({ dom: '{{ auth()->user()->role_as == "Admin" ? "Birtp" : "irtp" }}', "pageLength": -1 , responsive: true, ordering: false, order: []});
                    }
                });
            }
        }

       

        function edit_modal(id,key_value){
            var url = "{{route('manage_stock.edit_modal',":id")}}";
            url = url.replace(':id',id);
            $('#ajax_html').html('<div class="loader-box"><div class="loader-37"></div></div>');
            $.get(url,{key_value:key_value,stock_name:"{{$stock_name}}",in_out:"{{$in_out}}",unit_name:"{{$unit_name}}",average:"{{$average}}"}, function(data){
                $('#ajax_html').html(data);
                $('.js-example-basic-single').select2();
            });
        }

        $(document).on('submit','form',function(event){
            event.preventDefault();
            var form = event.target;
            var form_data = new FormData(form);
            var $submitBtn = $('form button[type="submit"]');
            if($submitBtn.hasClass('disabled')){
                return false;
            }
            $submitBtn.addClass('disabled');
            $.ajax({
                url: $(event.target).attr('action'),
                type: 'POST',
                data: form_data,
                processData: false,
                contentType: false,
                success: function(data){
                    if(data.result == 1){
                        $.notify({ title:'Success', message:data.message }, { type:'success', });
                        var page = Number($(".pages").find('span[aria-current="page"] span').text());
                        $('#stock_id').val('').trigger('change');
                        $('#quantity, #average').val('');
                        $submitBtn.removeClass('disabled');
                        $('#current_stock').text(0);
                        $('#news_stock').text(0);
                        get_datatable(page);
                        $('#edit_modal').modal('hide');
                        $("#stock_id").focus();
                    }else{
                        $.notify({ title:'Error', message:data.message }, { type:'danger', });
                        $submitBtn.removeClass('disabled');
                    }
                },
                error: function() { $submitBtn.html('Save').removeClass('disabled'); }
            });
        });

        function delete_stock(id){
            swal({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willDelete) => {
                if (willDelete) {
                    var url = "{{route('manage_stock.delete',":id")}}";
                    url = url.replace(':id',id);
                    $.get(url, function(data){
                        if(data.result == 1){
                            var page = Number($(".pages").find('span[aria-current="page"] span').text());
                            get_datatable(page);
                            $.notify({ title:'Deleted', message:data.message}, { type:'danger', });
                        }
                    })
                }
            })
        }


        function get_current_stock(id){
            var url = "{{route('manage_stock.get_current_stock',":id")}}";
            url = url.replace(':id',id);
            $.get(url, {stock_name: "{{$stock_name}}"}, function(data){
                if(data.result == 1){
                    $('#current_stock').text(data.current_average);
                    get_average($('#quantity').val()); // Refresh new stock calculation
                }
            })
        }

        function get_average(value){
            var avg_factor = "{{$average}}";
            var total_avg = (parseFloat(value) || 0) * parseFloat(avg_factor);
            $('#average').val(total_avg.toFixed(2));

            var current = parseFloat($('#current_stock').text()) || 0;
            var input_qty = parseFloat(value) * parseFloat(avg_factor) || 0;
            var in_out = "{{$in_out}}";
            
            var new_stock = 0;
            if(in_out == 'in'){
                new_stock = current + input_qty;
            } else {
                new_stock = current - input_qty;
            }
            $('#news_stock').text(new_stock.toFixed(2));

            if(in_out != 'in' && new_stock < 0){
                $('#add_data').prop('disabled', true).addClass('disabled');
                $('#news_stock').addClass('text-danger').removeClass('text-dark');
                // Optional: alert the user once if they exceed stock
                if(!window.notified_insufficient){
                    $.notify({ title:'Error', message:'Insufficient stock' }, { type:'danger', });
                    window.notified_insufficient = true;
                }
            } else {
                $('#add_data').prop('disabled', false).removeClass('disabled');
                $('#news_stock').addClass('text-dark').removeClass('text-danger');
                window.notified_insufficient = false;
            }
        }
    </script>
@endsection
