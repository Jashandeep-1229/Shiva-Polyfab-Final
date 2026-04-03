@extends('layouts.admin.app')

@section('title', 'Dashboard')

@section('css')
   
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">Dashboard</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row widget-grid">
        <div class="col-md-3">
          <div class="card small-widget"> 
            <div class="card-body primary"> <span class="f-light">Total Job Cards</span>
              <div class="d-flex align-items-end gap-1">
                <h4>{{ $total_job_cards }}</h4>
              </div>
              <div class="bg-gradient font-primary"> 
                <i data-feather="file-text"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card small-widget"> 
            <div class="card-body warning"> <span class="f-light">Pending Jobs</span>
              <div class="d-flex align-items-end gap-1">
                <h4>{{ $pending_job_cards }}</h4>
              </div>
              <div class="bg-gradient font-warning"> 
                <i data-feather="clock"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card small-widget"> 
            <div class="card-body info"> <span class="f-light">In Progress</span>
              <div class="d-flex align-items-end gap-1">
                <h4>{{ $progress_job_cards }}</h4>
              </div>
              <div class="bg-gradient font-info"> 
                <i data-feather="activity"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card small-widget"> 
            <div class="card-body success"> <span class="f-light">Low Stock Fabrics</span>
              <div class="d-flex align-items-end gap-1">
                <h4>{{ $low_stock_fabric }}</h4>
              </div>
              <div class="bg-gradient font-success"> 
                <i data-feather="alert-triangle"></i>
              </div>
            </div>
          </div>
        </div>
    <div class="row mt-4">
        <div class="col-xl-5">
            <div class="card shadow-sm border-0" style="border-radius: 15px; background: #fff;">
                <div class="card-header py-3" style="border-top-left-radius: 15px; border-top-right-radius: 15px; background: linear-gradient(135deg, #7366ff 0%, #a29bfe 100%);">
                    <h5 class="mb-0 text-white"><i class="fa fa-line-chart me-2"></i>Sales Performance Report</h5>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted small mb-4">Select an executive and date range to view their detailed performance dashboard.</p>
                    <form action="{{ route('dashboard') }}" method="GET">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-dark">Executive Name</label>
                            <select name="executive_id" class="form-select border shadow-none" style="background-color: #f8f9fa; color: #1f2937; height: 45px; border-radius: 8px;" required>
                                <option value="" style="color: #6b7280;">Choose Sales Executive...</option>
                                @foreach($executives as $ex)
                                    <option value="{{ $ex->id }}" style="color: #1f2937;">{{ $ex->name }} ({{ $ex->role_as }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <label class="form-label small text-muted">From Date</label>
                                <input type="date" name="from_date" value="{{ date('Y-m-d', strtotime('-7 days')) }}" class="form-control border shadow-none" style="background-color: #f8f9fa; color: #1f2937; height: 45px; border-radius: 8px;">
                            </div>
                            <div class="col-6">
                                <label class="form-label small text-muted">To Date</label>
                                <input type="date" name="to_date" value="{{ date('Y-m-d') }}" class="form-control border shadow-none" style="background-color: #f8f9fa; color: #1f2937; height: 45px; border-radius: 8px;">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 shadow-sm border-0" style="height: 50px; border-radius: 10px; background: linear-gradient(135deg, #7366ff 0%, #a29bfe 100%); font-weight: 700;">
                            Generate Report <i class="fa fa-arrow-right ms-2 f-14"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')

@endsection