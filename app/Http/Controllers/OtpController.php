<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class OtpController extends Controller
{
    public function verify(Request $request)
    {
        $user = Auth::user();
        $otp = $request->otp;

        if ($user->current_otp == $otp) {
            $user->is_device_verified = 1;
            $user->verified_device_id = $request->cookie('device_id');
            // $user->current_otp = null; // Clear it after success
            $user->save();

            return response()->json(['result' => 1, 'message' => 'Device verified successfully']);
        }

        return response()->json(['result' => -1, 'message' => 'Incorrect OTP. Please try again.']);
    }

    public function resend()
    {
        $user = Auth::user();
        $user->current_otp = rand(1000, 9999);
        $user->otp_created_at = now();
        $user->save();

        return response()->json(['result' => 1, 'message' => 'New OTP has been generated.']);
    }

    public function admin_index(Request $request)
    {
        if (Auth::user()->role_as != 'Admin') abort(403);
        
        $pending = \App\Models\User::where('role_as', '!=', 'Admin')
            ->where('is_device_verified', 0)
            ->whereNotNull('current_otp')
            ->orderBy('id', 'desc')
            ->get();

        if ($request->ajax()) {
            $html = '';
            if ($pending->count() == 0) {
                $html = '<div class="col-12 text-center p-5"><div class="alert alert-info">No pending login requests at the moment. All devices are verified.</div></div>';
            } else {
                foreach ($pending as $item) {
                    $html .= '
                    <div class="col-xl-4 col-md-6 mb-4 animate__animated animate__fadeIn">
                        <div class="card h-100 border-0 shadow-sm" style="border-radius: 15px; overflow: hidden; border-left: 5px solid #4f46e5 !important;">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-light-primary p-3 rounded-circle me-3">
                                        <i class="fa fa-user fs-4 text-primary"></i>
                                    </div>
                                    <div>
                                        <h5 class="card-title fw-bold mb-0 text-dark">'.$item->name.'</h5>
                                        <small class="text-muted">'.$item->role_as.' (ID: #'.$item->id.')</small>
                                    </div>
                                </div>
                                <hr class="my-3 opacity-10">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted small"><i class="fa fa-phone me-1"></i> Phone:</span>
                                        <span class="fw-bold small">'.($item->phone ?? "N/A").'</span>
                                    </div>
                                     <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted small"><i class="fa fa-clock-o me-1"></i> Last Active:</span>
                                        <span class="text-primary fw-bold small">'.$item->last_active_at->diffForHumans().'</span>
                                    </div>
                                    <div class="p-2 bg-light rounded small mt-2">
                                        <i class="fa fa-desktop me-1"></i> Device Info:<br>
                                        <span class="text-secondary" style="font-size: 11px;">'.($item->last_device_info ?: "Unspecified").'</span>
                                    </div>
                                </div>
                                <div class="bg-primary text-white text-center p-3 rounded shadow-sm">
                                    <div class="small mb-1 opacity-75">LOGIN OTP CODE</div>
                                    <div class="fs-1 fw-bold tracking-widest" style="letter-spacing: 12px;">'.$item->current_otp.'</div>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0 text-center pb-4">
                                <small class="text-muted"><i class="fa fa-shield"></i> Security Tip: Share this code <b>only</b> with the employee.</small>
                            </div>
                        </div>
                    </div>';
                }
            }
            return response()->json(['html' => $html, 'count' => $pending->count()]);
        }
            
        return view('admin.device_verification.index');
    }
}
