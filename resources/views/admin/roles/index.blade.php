@extends('user-master')

@section('title', 'Role & Permission')

@section('css')
   
@endsection

@section('style')

@endsection

@section('breadcrumb-title')
    <h3>Role & Permission</h3>
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">Dashboard</li>
    <li class="breadcrumb-item active">Latest Role & Permission</li>
@endsection
@php
    $permission = ['Dashboard','Slider','News','Important News','Daily Circular','Events','Latest Information','Category','Library','Personal Library','Gallery','Video Manager','Content Master','Academics','Useful Links','Upcoming Deadlines','Package','Members','Payment Orders','Quiz Library','Second Number Request','Meetings','New Logo Request','Ready Logo Request','Banner','Yearly to do List'];
@endphp
@section('content')
    <div class="container-fluid">
        <!-- Table Start -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex">
                    <h5 class="card-title">All Role & Permission</h5>
                    <a href="#" class="btn btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#add">Add Role & Permission</a>
                </div>
                    <div class="card-body">
                        <div class="dt-ext table-responsive table-border-vertical">
                            <table class="display" id="basic-2">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Role Name</th>
                                        <th>Permissiom</th>
                                        <th>Option</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $Sr = 1;
                                    @endphp
                                    @foreach ($roles as $item)
                                    <tr>
                                        <td class="text-middle">{{$Sr++}}</td>
                                        <td>{{$item->name }}</td>
                                        <td>{{$item->permission }}</td>
                                        <td class="text-middle">
                                            <a href="#" class="text-warning p-1" data-toggle="tooltip" title="Edit" data-bs-toggle="modal" data-bs-target="#edit-{{$item->id}}">
                                                <i data-feather="edit"></i>
                                            </a>
                                            
                                            <a id="Delete-{{$item->id}}" class="text-danger pointer p-1" data-toggle="tooltip" title="Delete">
                                                <i data-feather="trash-2"></i>
                                            </a>
                                            <script>
                                                $('#Delete-{{$item->id}}').click(function(){
                                                    Swal.fire({
                                                    title: 'Are you sure?',
                                                    text: "You won't be able to revert this!",
                                                    icon: 'question',
                                                    showCancelButton: true,
                                                    confirmButtonColor: '#3085d6',
                                                    cancelButtonColor: '#d33',
                                                    confirmButtonText: 'Yes, delete it!'
                                                    }).then((result) => {
                                                    if (result.isConfirmed) {
                                                        window.location.href = "{{ route('role_permission.delete',$item->id)}}";
                                                    }
                                                    });
                                                });
                                            </script>
                                        </td>
                                    </tr>
                                    @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- All Table End -->

       {{-- Modal Start Add --}}
       <div class="modal fade" id="add" tabindex="-1" role="dialog" aria-labelledby="add" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
            <form action="{{route('role_permission.store')}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                <h5 class="modal-title" id="add">Add Role & Permission</h5>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <h6>Role Name <span>*</span></h6>
                                <input type="text" class="form-control" name="name" id="name" value="{{old('name')}}" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <h6>Permission <span>*</span></h6>
                                <div class="row">
                                    @foreach($permission as $key => $data)
                                    <div class="col-md-3">
                                        <div class="form-check checkbox checkbox-primary mb-0">
                                            <input class="form-check-input" id="checkbox-primary-{{$key}}" @if($loop->first) checked onclick="return false" @endif type="checkbox" data-bs-original-title="" title="" name="permission[]" value="{{old('permission') ?? $data}}">
                                            <label class="form-check-label" for="checkbox-primary-{{$key}}">{{$data}}</label>
                                        </div>
                                    </div>
                                    {{-- <input type="checkbox" class="form-control" name="permission[]" id="permission" value="{{old('portal_link') ?? $data}}">{{$data}} --}}
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" type="submit">Save</button>
                </div>
            </form>
          </div>
        </div>
      </div>
    {{-- Modal End --}}
    {{-- Modal Start Edit --}}
    @foreach ($roles as $item)
    <div class="modal fade" id="edit-{{$item->id}}" tabindex="-1" role="dialog" aria-labelledby="edit-{{$item->id}}" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
            <form action="{{route('role_permission.update',$item->id)}}" method="post" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header">
                <h5 class="modal-title" id="edit-{{$item->id}}">Edit Role & Permission</h5>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                 <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <h6>Role Name <span>*</span></h6>
                                <input type="text" class="form-control" name="name" id="name" value="{{old('name') ?? $item->name ?? ''}}" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-3">

                                <h6>Permission<span>*</span></h6>
                                <div class="row">
                                    @foreach($permission as $key => $data)
                                    <div class="col-md-3">
                                        <div class="form-check checkbox checkbox-primary mb-0">
                                            <input class="form-check-input" id="checkbox-primary-edit-{{$key}}{{$item->id}}"  @if($loop->first) checked onclick="return false" @endif type="checkbox" name="permission[]" value="{{old('permission') ?? $data}}"  {{in_array($data,json_decode($item->permission)) ? 'checked':''}}>
                                            <label class="form-check-label" for="checkbox-primary-edit-{{$key}}{{$item->id}}">{{$data}}</label>
                                        </div>
                                    </div>
                                    {{-- <input type="checkbox" class="form-control" name="permission[]" id="permission" value="{{$data}}" {{in_array($data,json_decode($item->permission)) ? 'checked':''}}>{{$data}} --}}
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" type="submit">Save</button>
                </div>
            </form>
            </div>
        </div>
    </div>
    @endforeach
    {{-- Modal End --}}


        <script type="text/javascript">
            var session_layout = '{{ session()->get('layout') }}';
        </script>
    @endsection

    @section('script')
   
    @endsection
