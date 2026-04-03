<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - {{ config('app.name') }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/bootstrap.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/font-awesome.css') }}">
    <style>
        body {
            background: #f8fafc;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Rubik', sans-serif;
            margin: 0;
        }
        .error-container {
            max-width: 500px;
            width: 90%;
            background: #ffffff;
            padding: 50px 40px;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
            text-align: center;
            border: 1px solid #f1f5f9;
        }
        .icon-wrapper {
            width: 100px;
            height: 100px;
            background: #fff1f2;
            color: #e11d48;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 45px;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(225, 29, 72, 0.4); }
            70% { transform: scale(1.05); box-shadow: 0 0 0 15px rgba(225, 29, 72, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(225, 29, 72, 0); }
        }
        h2 {
            color: #1e293b;
            font-weight: 800;
            margin-bottom: 15px;
            letter-spacing: -0.5px;
        }
        p {
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 35px;
        }
        .btn-logout {
            background: #1e293b;
            color: #ffffff;
            border: none;
            padding: 14px 28px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .btn-logout:hover {
            background: #0f172a;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            color: #ffffff;
        }
        .support-text {
            margin-top: 40px;
            font-size: 13px;
            color: #94a3b8;
        }
        .code-display {
            font-family: monospace;
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 4px;
            color: #475569;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="icon-wrapper">
            <i class="fa fa-lock"></i>
        </div>
        <h2>Access Restricted</h2>
        <p>You do not have the required permissions to view this dashboard. Please contact your system administrator to request access.</p>
        
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-logout">
                <i class="fa fa-sign-out"></i>
                Log Out System
            </button>
        </form>

        <div class="support-text">
            Error Code: <span class="code-display">403_UNAUTHORIZED_DASHBOARD</span>
        </div>
    </div>
</body>
</html>
