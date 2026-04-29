@extends('layouts.admin.app')
@section('title', 'AI Design Details - Shiva Polyfab')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Design Details - #{{ $design->id }}</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('ai_studio.index') }}">AI Studio</a></li>
                    <li class="breadcrumb-item active">Design Details</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <!-- Specs & Info -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Client Information</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1 text-mutedSmall">NAME:</p>
                    <h5 class="mb-3">{{ $design->customer_name }}</h5>
                    
                    @if($design->contact_no)
                    <p class="mb-1 text-mutedSmall">CONTACT:</p>
                    <h5 class="mb-3">{{ $design->contact_no }}</h5>
                    @endif

                    <div class="p-3 bg-light rounded border">
                        <p class="mb-1 text-mutedSmall font-weight-bold">EXTRACTED SPECS:</p>
                        @php $data = $design->ai_parsed_data; @endphp
                        @if($data)
                        <ul class="list-unstyled mb-0">
                            <li><i class="fa fa-tag me-2 text-primary"></i> <strong>Type:</strong> {{ ucfirst($data['job_type'] ?? 'New') }}</li>
                            <li><i class="fa fa-expand me-2 text-primary"></i> <strong>Size:</strong> {{ \App\Models\SizeMaster::find($data['size_id'])?->name ?? 'N/A' }}</li>
                            <li><i class="fa fa-palette me-2 text-primary"></i> <strong>Color:</strong> {{ \App\Models\ColorMaster::find($data['color_id'])?->name ?? 'N/A' }}</li>
                            <li><i class="fa fa-box me-2 text-primary"></i> <strong>BOPP:</strong> {{ \App\Models\Bopp::find($data['bopp_id'])?->name ?? 'N/A' }}</li>
                            <li><i class="fa fa-layer-group me-2 text-primary"></i> <strong>Fabric:</strong> {{ \App\Models\Fabric::find($data['fabric_id'])?->name ?? 'N/A' }}</li>
                            <li><i class="fa fa-hashtag me-2 text-primary"></i> <strong>Quantity:</strong> {{ $data['no_of_pieces'] ?? 0 }} pcs</li>
                        </ul>
                        @endif
                    </div>
                </div>
                <div class="card-footer">
                    @if($design->status == 'Draft' || $design->status == 'Shared')
                    <button class="btn btn-success btn-lg w-100" onclick="approveDesign()">
                        <i class="fa fa-check-circle me-1"></i> Approve Design Now
                    </button>
                    @elseif($design->status == 'Approved')
                    <a href="{{ route('ai_studio.convert', $design->id) }}" class="btn btn-primary btn-lg w-100">
                        <i class="fa fa-cogs me-1"></i> Convert to Job Card
                    </a>
                    @else
                    <div class="alert alert-info text-center mb-0">
                        <i class="fa fa-info-circle me-1"></i> Already Converted to Job Card
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Design Gallery -->
        <div class="col-md-8">
            <div class="card mb-4 min-vh-50">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Design Mockups Gallery</h5>
                    <span class="badge @if($design->status == 'Approved') bg-success @else bg-warning @endif p-2">Status: {{ $design->status }}</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if(!empty($design->design_mockups))
                            @foreach($design->design_mockups as $image)
                            <div class="col-md-6 mb-3">
                                <div class="p-2 border rounded bg-white box-shadow-sm h-100 d-flex flex-column">
                                    <img src="{{ str_contains($image, 'http') ? $image : asset($image) }}" class="img-fluid rounded mb-2" style="max-height: 400px; object-fit: contain;">
                                    <div class="mt-auto d-flex justify-content-between">
                                        <a href="{{ str_contains($image, 'http') ? $image : asset($image) }}" target="_blank" class="btn btn-xs btn-outline-primary"><i class="fa fa-search-plus"></i> Zoom</a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="col-12 text-center py-5">
                                <i class="fa fa-image fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No mockups uploaded yet. Edit this intelligence flow to add designs.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-light">
                    <h5>Original Client Text Requirements</h5>
                </div>
                <div class="card-body">
                    <p class="lead" style="font-style: italic; color: #555;"> "{{ $design->requirements }}"</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom_javascript')
<script>
function approveDesign() {
    if (confirm("Are you sure you want to Approve this design and parameters? Once approved, it can be converted to a real Job Card.")) {
        $.ajax({
            url: "{{ route('ai_studio.approve', $design->id) }}",
            method: "POST",
            data: { _token: "{{ csrf_token() }}" },
            success: function(response) {
                if (response.result === 1) {
                    alert(response.message);
                    location.reload();
                }
            }
        });
    }
}
</script>
@endsection
