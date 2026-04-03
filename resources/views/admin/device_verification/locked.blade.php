<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Verification - {{ config('app.name') }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/bootstrap.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/font-awesome.css') }}">
    <script src="{{ asset('assets/js/jquery-3.5.1.min.js') }}"></script>
    <style>
        body {
            background: #f1f5f9;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Rubik', sans-serif;
        }
        .lock-container {
            max-width: 450px;
            width: 90%;
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            text-align: center;
        }
        .icon-box {
            width: 80px;
            height: 80px;
            background: rgba(79, 70, 229, 0.1);
            color: #4f46e5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 35px;
        }
        .otp-box {
            width: 60px;
            height: 65px;
            font-size: 28px;
            font-weight: bold;
            text-align: center;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            margin: 0 5px;
        }
        .otp-box:focus {
            border-color: #4f46e5;
            outline: none;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        .btn-verify {
            background: #4f46e5;
            color: white;
            border: none;
            padding: 15px;
            border-radius: 12px;
            font-weight: bold;
            width: 100%;
            margin-top: 25px;
            transition: all 0.3s;
        }
        .btn-verify:hover { background: #4338ca; }
        .btn-verify.disabled { opacity: 0.5; cursor: not-allowed; }
    </style>
</head>
<body>
    <div class="lock-container">
        <div class="icon-box">
            <i class="fa fa-shield"></i>
        </div>
        <h3 class="fw-bold text-dark">Device Verification</h3>
        <p class="text-muted mb-4">Your current device is not recognized. Please provide the 4-digit security code from your Admin to continue.</p>

        <div class="d-flex justify-content-center mb-3">
            <input type="text" maxlength="1" class="otp-box" id="o1" autocomplete="off">
            <input type="text" maxlength="1" class="otp-box" id="o2" autocomplete="off">
            <input type="text" maxlength="1" class="otp-box" id="o3" autocomplete="off">
            <input type="text" maxlength="1" class="otp-box" id="o4" autocomplete="off">
        </div>

        <div id="error-msg" class="text-danger small mb-3 d-none">Incorrect verification code.</div>

        <button id="btn-submit" class="btn-verify disabled">VERIFY DEVICE</button>
        
        <!-- <div class="mt-3">
            <button id="btn-resend" class="btn btn-link text-decoration-none small fw-bold" style="color: #4f46e5;">
                <i class="fa fa-refresh me-1"></i> SEND OTP AGAIN
            </button>
            <div id="resend-timer" class="small text-muted d-none">Resend available in <span id="timer-sec">30</span>s</div>
        </div> -->

        <hr class="my-4 opacity-50">

        <form action="{{ route('logout') }}" method="POST" id="logout-form">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm border-0 fw-bold">
                <i class="fa fa-sign-out me-1"></i> LOGOUT & TRY LATER
            </button>
        </form>
        
        <p class="mt-4 small text-muted">
            <i class="fa fa-info-circle"></i> Security is active. Your activity is being monitored by Admin.
        </p>
    </div>

    <script>
        $(document).ready(function() {
            $('.otp-box').first().focus();

            $('.otp-box').on('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value && $(this).next('.otp-box').length) $(this).next().focus();
                checkForm();
            });

            $('.otp-box').on('keydown', function(e) {
                if (e.key === 'Backspace' && !this.value) $(this).prev().focus();
            });

            function checkForm() {
                let code = '';
                $('.otp-box').each(function() { code += $(this).val(); });
                
                if (code.length === 4) {
                    $('#btn-submit').removeClass('disabled');
                    // Auto submit only if it wasn't just cleared from incorrect attempt
                    if(!$('#btn-submit').data('submitting')) {
                        $('#btn-submit').click();
                    }
                } else {
                    $('#btn-submit').addClass('disabled');
                }
            }

            $('#btn-submit').click(function() {
                if ($(this).hasClass('disabled')) return;
                
                let code = '';
                $('.otp-box').each(function() { code += $(this).val(); });
                
                $(this).addClass('disabled').text('VERIFYING...').data('submitting', true);
                $('#error-msg').addClass('d-none');

                $.post("{{ route('otp.verify') }}", {
                    _token: "{{ csrf_token() }}",
                    otp: code
                }, function(res) {
                    if (res.result == 1) {
                        location.reload();
                    } else {
                        $('#error-msg').removeClass('d-none');
                        $('#btn-submit').removeClass('disabled').text('VERIFY DEVICE').data('submitting', false);
                        $('.otp-box').val('').first().focus();
                    }
                });
            });

            // Resend OTP logic
            $('#btn-resend').click(function(e) {
                e.preventDefault();
                let $btn = $(this);
                if($btn.hasClass('disabled')) return;

                $btn.addClass('disabled').html('<i class="fa fa-spinner fa-spin me-1"></i> GENERATING...');
                
                $.post("{{ route('otp.resend') }}", {
                    _token: "{{ csrf_token() }}"
                }, function(res) {
                    if (res.result == 1) {
                        $btn.addClass('d-none');
                        $('#resend-timer').removeClass('d-none');
                        startTimer(30);
                        alert("New OTP has been sent to Admin. Please ask them for the new code.");
                    } else {
                        $btn.removeClass('disabled').html('<i class="fa fa-refresh me-1"></i> SEND OTP AGAIN');
                        alert("Failed to resend OTP. Please try again.");
                    }
                });
            });

            function startTimer(duration) {
                let timer = duration, seconds;
                let interval = setInterval(function () {
                    seconds = parseInt(timer % 60, 10);
                    $('#timer-sec').text(seconds);

                    if (--timer < 0) {
                        clearInterval(interval);
                        $('#btn-resend').removeClass('d-none').removeClass('disabled').html('<i class="fa fa-refresh me-1"></i> SEND OTP AGAIN');
                        $('#resend-timer').addClass('d-none');
                    }
                }, 1000);
            }
        });
    </script>
</body>
</html>
