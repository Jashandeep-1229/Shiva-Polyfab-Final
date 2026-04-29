@extends('layouts.admin.app')
@section('title', 'AI Intelligence Studio - Shiva Polyfab')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>🤖 AI Intelligence Studio</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item active">AI Studio</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <!-- The Magic Box (AI Smart Entry) -->
        <div class="col-md-12">
            <div class="card" style="border-radius: 15px; border-left: 5px solid #6366F1; overflow: hidden;">
                <div class="card-body">
                    <h5 class="mb-3"><i class="fa fa-magic text-primary me-2"></i> Magic Smart Entry</h5>
                    <div class="row">
                        <div class="col-md-8">
                            <label class="form-label font-weight-bold">Type customer requirements here (e.g. 50,000 Blue 14x18 D-Cut loop bags on White fabric 25 micron BOPP)</label>
                            <div class="position-relative">
                                <textarea id="ai_magic_text" class="form-control" rows="4" placeholder="AI will automatically extract Specs & Generate Designs..." style="font-size: 16px; border: 2px solid #e0e0e0; border-radius: 12px; resize: vertical; padding-bottom: 40px;"></textarea>
                                <button type="button" id="trigger_magic_btn" class="btn btn-primary btn-sm position-absolute" style="bottom: 10px; right: 10px; border-radius: 8px;">
                                    <i class="fa fa-magic me-1"></i> Magic Generate
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div id="ai_parsing_status" class="alert alert-light border d-none" style="height: 100%; display: flex; flex-direction: column; justify-content: center;">
                                <h6 class="text-indigo mb-2"><i class="fa fa-rocket me-1"></i> AI Extracting...</h6>
                                <div id="ai_insights" class="small">
                                    <!-- AI Results will appear here -->
                                </div>
                            </div>
                            <div id="ai_initial_tip" class="p-3 bg-light rounded" style="height: 100%; display: flex; align-items: center; justify-content: center;">
                                <p class="text-muted text-center mb-0"><i class="fa fa-info-circle me-1"></i> Start typing to see AI Magic logic in action.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Design Ideas (Dynamic) -->
        <div class="col-md-12 d-none mt-3" id="design_ideas_section">
            <div class="card shadow-sm" style="border-radius: 15px; background: #f8fafc; border: 1px solid #e2e8f0;">
                <div class="card-body">
                    <h5 class="mb-4 d-flex align-items-center"><i class="fa fa-paint-brush text-indigo me-2"></i> <span>AI Generated Design Concepts</span> <span class="badge bg-success ms-2" style="font-size: 10px;">PRO</span></h5>
                    
                    <div id="design_loader" class="text-center py-5 d-none">
                        <div class="loader-box"><div class="loader-37"></div></div>
                        <p class="mt-3 text-muted">AI is crafting unique designs for you...</p>
                    </div>

                    <div class="row g-3" id="design_grid">
                        <!-- Designs will appear here -->
                    </div>

                    <div id="ai_description_box" class="mt-4 p-3 bg-white rounded border d-none" style="border-left: 4px solid #6366f1 !important;">
                        <h6 class="font-weight-bold text-dark"><i class="fa fa-quote-left me-2 text-muted"></i> AI Design Analysis:</h6>
                        <div id="ai_design_text" class="text-muted small" style="line-height: 1.6;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Design Progress List -->
        <div class="col-md-12 mt-3">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Recent AI Designs & Drafts</h5>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newAiDesignModal">
                        <i class="fa fa-plus-circle me-1"></i> New Intelligence Flow
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Requirements Summary</th>
                                    <th>Status</th>
                                    <th>Approved Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($designs as $design)
                                <tr>
                                    <td>#{{ $design->id }}</td>
                                    <td><strong>{{ $design->customer_name }}</strong><br><small>{{ $design->contact_no }}</small></td>
                                    <td>{{ Str::limit($design->requirements, 60) }}</td>
                                    <td>
                                        <span class="badge @if($design->status == 'Draft') bg-secondary @elseif($design->status == 'Shared') bg-info @elseif($design->status == 'Approved') bg-success @else bg-primary @endif">
                                            {{ $design->status }}
                                        </span>
                                    </td>
                                    <td>{{ $design->approval_date ? $design->approval_date->format('d M, Y') : '---' }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('ai_studio.show_design', $design->id) }}" class="btn btn-sm btn-info text-white"><i class="fa fa-eye"></i> View</a>
                                            @if($design->status == 'Approved')
                                            <a href="{{ route('ai_studio.convert', $design->id) }}" class="btn btn-sm btn-success"><i class="fa fa-cog"></i> Create Job Card</a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $designs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for New Design Intelligence -->
<div class="modal fade" id="newAiDesignModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New AI Design Intelligence Flow</h5>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="ai_design_form" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Customer Name</label>
                            <input type="text" name="customer_name" class="form-control" placeholder="e.g. RK Traders" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contact No</label>
                            <input type="text" name="contact_no" class="form-control" placeholder="Optional">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Natural Requirements (The "Magic" Box)</label>
                            <textarea id="modal_ai_text" name="requirements" class="form-control" rows="3" placeholder="Paste or type requirements here..." required></textarea>
                        </div>
                        
                        <div id="ai_auto_result_container" class="col-md-12 d-none mb-3">
                            <div class="p-3 rounded border bg-light">
                                <h6 class="text-primary Small font-weight-bold mb-2">AI Extraction Proposal:</h6>
                                <div id="ai_detected_tags" class="d-flex flex-wrap gap-2 mb-2">
                                    <!-- Tags will appear here -->
                                </div>
                                <input type="hidden" name="ai_parsed_data" id="ai_parsed_json">
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Upload Design Mockups (Select Multiple)</label>
                            <input type="file" name="design_mockups[]" class="form-control" multiple accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-primary" type="submit">Save Design Flow</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('custom_javascript')
<script>
$(document).ready(function() {
    $('#ai_magic_text, #modal_ai_text').on('keyup', function() {
        const text = $(this).val();
        const isModal = $(this).attr('id') === 'modal_ai_text';
        
        clearTimeout(typingTimer);
        if (text.length > 5) {
            typingTimer = setTimeout(() => processMagicEntry(text, isModal), doneTypingInterval);
        } else if (!isModal) {
            $('#ai_parsing_status').addClass('d-none');
            $('#ai_initial_tip').removeClass('d-none');
            $('#design_ideas_section').addClass('d-none');
        }
    });

    $('#trigger_magic_btn').on('click', function() {
        const text = $('#ai_magic_text').val();
        if (text.length > 5) {
            processMagicEntry(text, false);
        } else {
            alert("Please type more requirements first!");
        }
    });

    function processMagicEntry(text, isModal) {
        if (!isModal) {
            $('#ai_initial_tip').addClass('d-none');
            $('#ai_parsing_status').removeClass('d-none');
            $('#ai_insights').html("<div class='text-center py-2'><div class='loader-box'><div class='loader-37'></div></div><p class='small mt-1'>AI is thinking...</p></div>");
        }

        $.ajax({
            url: "{{ route('ai_studio.smart_parse') }}",
            method: "POST",
            data: { _token: "{{ csrf_token() }}", text: text },
            success: function(response) {
                if (response.status === 'success') {
                    const data = response.data;
                    let html = "";
                    let tagsHtml = "";
                    
                    if (data.found_items.length > 0) {
                        data.found_items.forEach(item => {
                            html += `<div class='mb-1'><i class='fa fa-check-circle text-success me-1'></i>${item}</div>`;
                            tagsHtml += `<span class='badge bg-indigo text-white p-2 mb-1 me-1'>${item}</span>`;
                        });
                    }

                    if (data.no_of_pieces) {
                        html += `<div class='mb-1'><i class='fa fa-check-circle text-success me-1'></i>Quantity: ${data.no_of_pieces} pcs</div>`;
                        tagsHtml += `<span class='badge bg-success p-2 mb-1 me-1'>${data.no_of_pieces} pcs</span>`;
                    }

                    if (data.job_type) {
                        tagsHtml += `<span class='badge bg-primary p-2 mb-1 me-1'>Type: ${data.job_type}</span>`;
                    }

                    if (!isModal) {
                        $('#ai_insights').html(html || "<span class='text-muted'>No parameters detected. Try specifying size/color.</span>");

                        // Trigger Design Ideas
                        $('#design_ideas_section').removeClass('d-none');
                        $('#design_loader').removeClass('d-none');
                        $('#design_grid').empty();
                        $('#ai_description_box').addClass('d-none');

                        $.ajax({
                            url: "{{ route('ai_studio.generate_designs') }}",
                            method: "POST",
                            data: { _token: "{{ csrf_token() }}", requirement: text },
                            success: function(res) {
                                $('#design_loader').addClass('d-none');
                                if (res.status === 'success') {
                                    let gHtml = "";
                                    res.designs.forEach((img, idx) => {
                                        gHtml += `
                                        <div class="col-md-3">
                                            <div class="card h-100 border-0 shadow-sm overflow-hidden" style="border-radius:12px; background: #fff;">
                                                <div style="aspect-ratio: 1/1; overflow: hidden; background: #f0f0f0;">
                                                    <a href="${img}" target="_blank">
                                                        <img src="${img}" class="img-fluid w-100" alt="Design ${idx+1}" style="transition: transform .3s; object-fit: cover; height: 100%;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                                                    </a>
                                                </div>
                                                <div class="card-footer bg-white border-0 text-center py-2">
                                                    <button type="button" class="btn btn-outline-primary btn-xs px-3" style="font-size: 10px;" onclick="useDesign('${img}', '${text.replace(/'/g, "\\'")}')">Save to Cloud</button>
                                                </div>
                                            </div>
                                        </div>`;
                                    });
                                    $('#design_grid').html(gHtml);
                                    $('#ai_design_text').html(res.ai_description.replace(/\n/g, '<br>'));
                                    $('#ai_description_box').removeClass('d-none');
                                }
                            },
                            error: function() { $('#design_loader').addClass('d-none'); }
                        });
                    } else {
                        $('#ai_auto_result_container').removeClass('d-none');
                        $('#ai_detected_tags').html(tagsHtml || "No specific matches found yet.");
                        $('#ai_parsed_json').val(JSON.stringify(data));
                    }
                }
            },
            error: function() {
                if (!isModal) $('#ai_insights').html("<span class='text-danger'>AI Parsing failed.</span>");
            }
        });
    }

    // Form Submission
    $('#ai_design_form').submit(function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        $.ajax({
            url: "{{ route('ai_studio.store') }}",
            method: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.result === 1) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert("Error saving design flow.");
                }
            }
        });
    });
});

function useDesign(imgUrl, reqText) {
    let cust = prompt("Who is this design for?", "AI Concept Customer");
    if (!cust) return;

    $.ajax({
        url: "{{ route('ai_studio.store') }}",
        method: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            customer_name: cust,
            requirements: reqText,
            mockup_urls: [imgUrl],
            status: 'Approved'
        },
        success: function(res) {
            if (res.result === 1) {
                alert("Design saved to your cloud library! You can now convert it to a Job Card.");
                location.reload();
            }
        }
    });
}
</script>
<style>
    .bg-indigo { background-color: #6366f1 !important; color: #fff; }
    .text-indigo { color: #6366f1 !important; }
    .btn-outline-indigo { border-color: #6366f1; color: #6366f1; }
    .btn-outline-indigo:hover { background-color: #6366f1; color: #fff; }
    .btn-xs { padding: 4px 10px; font-size: 11px; }
</style>
@endsection
