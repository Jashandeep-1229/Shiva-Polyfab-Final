@extends('layouts.admin.app')

@section('title', 'GSM Calculator')

@section('css')
<style>
    .gsm-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        background: linear-gradient(145deg, #ffffff, #f8f9fa);
    }
    .gsm-title {
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 25px;
        border-left: 5px solid #6366f1;
        padding-left: 15px;
    }
    .result-box {
        background: #fdfdfd;
        border-radius: 12px;
        padding: 20px;
        margin-top: 30px;
        border: 1px solid #e0e6ed;
        transition: all 0.3s ease;
    }
    .result-box.active {
        background: #f0f7ff;
        border-color: #3b82f6;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.1);
    }
    .result-item {
        margin-bottom: 10px;
    }
    .result-label {
        font-size: 0.9rem;
        color: #64748b;
        margin-bottom: 5px;
    }
    .result-value {
        font-size: 1.4rem;
        font-weight: 800;
        color: #1e293b;
    }
    .form-control {
        border-radius: 8px;
        border: 1px solid #d1d5db;
        padding: 10px 15px;
        transition: all 0.3s ease;
    }
    .form-control:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
    .badge-premium {
        background: #6366f1;
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .form-label {
        font-weight: 600;
        font-size: 0.85rem;
    }
</style>
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">Utilities</li>
    <li class="breadcrumb-item active">GSM Calculator</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-11">
            <div class="card gsm-card">
                <div class="card-body p-5">
                    <div class="gsm-title d-flex align-items-center justify-content-between">
                        <h4 class="mb-0">GSM Weight Calculator</h4>
                        <span class="badge-premium">Professional Utility</span>
                    </div>

                    <div class="row g-4" id="calculator_form" oninput="calculate_gsm()">
                        <div class="col-md-3">
                            <label class="form-label text-muted">Bag Type</label>
                            <select class="form-control" name="box_type" id="box_type" onchange="get_field(this.value)" required>
                                <option value="">Select Type</option>
                                <option value="Box Bag">Box Bag</option>
                                <option value="Loop Bag">Loop Bag</option>
                                <option value="DCut Bag">DCut Bag</option>
                                <option value="Twist Bag">Twist Bag</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted">Width</label>
                            <input type="number" step="any" name="width" id="width" placeholder="W" class="form-control" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted">Length</label>
                            <input type="number" step="any" name="length" id="length" placeholder="L" class="form-control" readonly>
                        </div>
                        <div class="col-md-2" id="guzzete_div" style="display: none">
                            <label class="form-label text-muted">Guzzete</label>
                            <input type="number" step="any" name="guzzete" id="guzzete" placeholder="G" class="form-control" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted">GSM Value</label>
                            <input type="number" step="any" name="gsm_value" id="gsm_value" placeholder="GSM" class="form-control">
                        </div>
                    </div>

                    <div id="result_container" class="result-box" style="display: none">
                        <div class="row text-center">
                            <div class="col-md-6 border-end">
                                <div class="result-label">Weight Per Piece</div>
                                <div class="result-value text-primary" id="weight_pcs">-</div>
                                <small class="text-muted">grams (approx)</small>
                            </div>
                            <div class="col-md-6">
                                <div class="result-label">Pieces In 1 KG</div>
                                <div class="result-value text-success" id="pcs_kg">-</div>
                                <small class="text-muted">quantity (rounded up)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    function get_field(value) {
        let width = $('#width');
        let length = $('#length');
        let guzzete = $('#guzzete');
        let gsm_value = $('#gsm_value');
        let guzzete_div = $('#guzzete_div');

        if (value == 'Box Bag' || value == 'Twist Bag') {
            width.prop('readonly', false);
            length.prop('readonly', false);
            guzzete.prop('readonly', false);
            guzzete_div.fadeIn();
        } else {
            width.prop('readonly', false);
            length.prop('readonly', false);
            guzzete.val('');
            guzzete.prop('readonly', true);
            guzzete_div.fadeOut();
        }

        // Default GSMs based on industry standards
        if (value == 'Box Bag') gsm_value.val('110');
        else if (value) gsm_value.val('80');
        
        calculate_gsm(); // Re-calculate on change
    }

    async function calculate_gsm() {
        var box_type = $('#box_type').val();
        var width = parseFloat($('#width').val()) || 0;
        var length = parseFloat($('#length').val()) || 0;
        var guzzete = parseFloat($('#guzzete').val()) || 0;
        var gsm = parseFloat($('#gsm_value').val()) || 0;

        // Reset display if primary fields are missing
        if (!box_type || width <= 0 || length <= 0 || gsm <= 0) {
            $('#result_container').fadeOut();
            return;
        }

        // Require guzzete for specific bag types
        if ((box_type == 'Box Bag' || box_type == 'Twist Bag') && guzzete <= 0) {
            $('#result_container').fadeOut();
            return;
        }

        var folding = 0;
        var extra_gram = 3;
        var material_weight = 1.5;

        // Logic Mapping
        if (box_type == 'Box Bag') {
            folding = 1.5;
        } else if (box_type == 'Loop Bag') {
            folding = 1;
            guzzete = 0;
        } else if (box_type == 'DCut Bag') {
            folding = 3;
            extra_gram = 0;
            guzzete = 0;
        } else if (box_type == 'Twist Bag') {
            folding = 0;
        }

        var extra_again = guzzete > 0 ? 0.5 : 0;
        var new_width = width + guzzete + extra_again;

        // Specific Industry Constraints
        if (new_width <= 13 || new_width >= 25) {
            $('#result_container').fadeOut();
            return;
        }

        var new_length;
        if (box_type == 'Twist Bag') {
            new_length = (length + folding) * 2;
        } else {
            new_length = (length + (guzzete / 2) + folding) * 2;
        }

        try {
            var again_new_length = await get_length_ajax(new_width, new_length);
            
            // Result Filtering
            if (again_new_length >= 30 && again_new_length <= 46) {
                $('#result_container').fadeOut();
                return;
            }

            // Calculation Core
            var total_calculate = new_width * again_new_length * gsm * 4;
            var per_pcs_gm = (total_calculate / 3100) + extra_gram + material_weight;
            
            // Rounding up pieces to next integer (e.g., 27.3 -> 28)
            var per_pc_kg = Math.ceil(1000 / per_pcs_gm);

            // Display Results
            $('#weight_pcs').text(per_pcs_gm.toFixed(2) + 'g');
            $('#pcs_kg').text(per_pc_kg);
            $('#result_container').addClass('active').fadeIn();

        } catch (e) {
            console.error(e);
            $('#result_container').fadeOut();
        }
    }

    function get_length_ajax(new_width, new_length) {
        var url = "{{ route('gsm_calculator.get_length') }}";
        return new Promise(function(resolve, reject) {
            $.get(url, { width: new_width, length: new_length }, function(data) {
                if (data) {
                    resolve(data / 2); 
                } else {
                    resolve(new_length / 2); // Intelligent fallback
                }
            });
        });
    }
</script>
@endsection
