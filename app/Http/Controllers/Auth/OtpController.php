<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\OtpNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class OtpController extends Controller
{
    /**
     * Send OTP to user's phone or email
     */
    public function send(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'type' => 'required|in:email,sms',
        ]);

        $key = 'otp.send.' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'email' => "Too many OTP requests. Please try again in {$seconds} seconds."
            ]);
        }

        RateLimiter::hit($key, 300); // 5 minutes

        $user = User::where('email', $request->email)->first();
        $otp = $user->generateOtp();

        // Send OTP notification
        $user->notify(new OtpNotification($otp, $request->type));

        return back()->with('status', 'OTP has been sent to your ' . $request->type);
    }

    /**
     * Verify OTP
     */
    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string|size:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user->verifyOtp($request->otp)) {
            return back()->withErrors([
                'otp' => 'Invalid or expired OTP.'
            ]);
        }

        $user->clearOtp();

        // Mark email as verified if not already
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        Auth::login($user, $request->boolean('remember', false));

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Show OTP verification form
     */
    public function show(): Response
    {
        return Inertia::render('Auth/VerifyOtp');
    }
}