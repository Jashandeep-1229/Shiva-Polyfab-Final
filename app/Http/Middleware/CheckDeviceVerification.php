<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class CheckDeviceVerification
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        
        if (!$user || $user->role_as == 'Admin') {
            return $next($request);
        }

        // IMPORTANT: If this is the actual verification request, don't generate a new code!
        if ($request->is('otp/verify') || $request->is('admin/otp/verify')) {
             return $next($request);
        }

        $deviceId = $request->cookie('device_id');
        
        // Generate device ID if not exists
        if (!$deviceId) {
            $deviceId = Str::random(40);
            Cookie::queue('device_id', $deviceId, 60 * 24 * 365); // 1 year
        }

        // Check if device matches and is verified
        if ($user->is_device_verified && $user->verified_device_id === $deviceId) {
            return $next($request);
        }

        // Always update last active time for users needing verification
        // This helps Admin see who is currently trying to login
        $user->last_active_at = now();

        // If we reach here, the device is NOT verified
        // Generate/Update OTP every time they hit this screen to ensure Admin sees a fresh request
        $user->current_otp = rand(1000, 9999);
        $user->otp_created_at = now();
        $user->last_active_at = now();
        $user->is_device_verified = 0;
        
        // Capture Device Info manually
        $user->last_device_info = $request->header('User-Agent');
        $user->save();

        // BLOCK THE PAGE SERVER-SIDE
        // Do not let the request continue to the regular controller
        return response()->view('admin.device_verification.locked');
    }
}
