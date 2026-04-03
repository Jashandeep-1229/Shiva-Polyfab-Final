@extends('layouts.admin.app')

@section('title', 'Website Setting')

@section('css')

@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">Settings</li>
@endsection
@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h4>Website Setting</h4> 
            </div>
            <div class="card-body">
                <form action="{{ route('website.setting.insert') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <h6>Logo Light:</h6>
                            <div class="row">
                                <div class="col-8">
                                    <input type="file" class="form-control" name="logo_light" id="" accept="image/*">
                                </div>
                                <div class="col-4 bg-dark">
                                    <a href="{{url(env('APP_LOGO_LIGHT'))}}"><img src="{{asset(env('APP_LOGO_LIGHT'))}}" alt="No Image" width="100px"></a>
                                </div>
                            </div>
                            <label>(Only jpg, jpeg and png are allowed) <span>Dimensions: 560*288px</span></label>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <h6>Logo Dark:</h6>
                            <div class="row">
                                <div class="col-8">
                                    <input type="file" class="form-control" name="logo_dark" id="" accept="image/*">
                                </div>
                                <div class="col-4">
                                    <a href="{{url(env('APP_LOGO_DARK'))}}"><img src="{{asset(env('APP_LOGO_DARK'))}}" alt="No Image" width="100px"></a>
                                </div>
                            </div>
                            <label>(Only jpg, jpeg and png are allowed) <span>Dimensions: 560*288px</span></label>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <h6>Website Name: <span>*</span></h6>
                            <input type="text" class="form-control" name="name" id="" value="{{ env('APP_NAME') }}" required>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <h6>Fav Icon:</h6>
                            <div class="row">
                                <div class="col-8">
                                    <input type="file" class="form-control" name="favicon" id="" accept="image/*">
                                </div>
                                <div class="col-4">
                                    <a href="{{url(env('APP_FAVICON'))}}"><img src="{{asset(env('APP_FAVICON'))}}" alt="No Image" width="50px"></a>
                                </div>
                            </div>
                            <label>(Only jpg, jpeg and png are allowed) <span>Dimensions: 50*50px</span></label>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <h5>Production Delay Limits (Dashboard)</h5>
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <h6>Cylinder Limit (Days):</h6>
                            <input type="number" class="form-control" name="cylinder_limit" value="{{ env('CYLINDER_LIMIT') }}" required>
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <h6>Printing Limit (Days):</h6>
                            <input type="number" class="form-control" name="printing_limit" value="{{ env('PRINTING_LIMIT') }}" required>
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <h6>Lamination Limit (Days):</h6>
                            <input type="number" class="form-control" name="lamination_limit" value="{{ env('LAMINATION_LIMIT') }}" required>
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <h6>Cutting Limit (Days):</h6>
                            <input type="number" class="form-control" name="cutting_limit" value="{{ env('CUTTING_LIMIT') }}" required>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    @endsection

@section('script')

@endsection



