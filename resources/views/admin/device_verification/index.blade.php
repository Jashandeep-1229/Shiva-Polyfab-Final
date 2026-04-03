@extends('layouts.admin.app')

@section('title', 'Pending Login Verifications')

@section('content')
<div class="container-fluid">
    <div class="row" id="pending-verifications-container">
        <!-- Data will be loaded here via AJAX for real-time updates -->
        <div class="col-12 text-center p-5 mt-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3">Monitoring login requests in real-time...</p>
        </div>
    </div>
</div>

<!-- Hidden sound for new request -->
<audio id="notification-sound" preload="auto">
    <source src="https://assets.mixkit.co/active_storage/sfx/2358/2358-preview.mp3" type="audio/mpeg">
</audio>

@endsection

@section('script')
<script>
    let lastCount = 0;

    function loadVerifications() {
        $.ajax({
            url: "{{ route('admin.device_verifications') }}",
            type: "GET",
            data: { ajax: 1 },
            success: function(response) {
                $('#pending-verifications-container').html(response.html);
                
                // Play sound if new request arrives
                let currentCount = response.count;
                if (currentCount > lastCount) {
                    try {
                        document.getElementById('notification-sound').play();
                    } catch(e) {}
                }
                lastCount = currentCount;
            }
        });
    }

    $(document).ready(function() {
        loadVerifications();
        // Poll every 5 seconds for real-time effect
        setInterval(loadVerifications, 5000);
    });
</script>
@endsection
