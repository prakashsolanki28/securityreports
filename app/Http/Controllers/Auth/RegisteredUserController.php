<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailOtp;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */

    public function sendOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|string|lowercase|email|max:255',
        ]);

        $email = $request->email;
        $request->session()->put('email', $email);
        // Generate/send OTP

        if (EmailOtp::hasExceededOtpLimit($email)) {
            return redirect()->route('register')->withErrors(['email' => 'You have reached the OTP request limit for today. Please try again after 24 hours.']);
        }

        EmailOtp::generateAndSend($email);

        return redirect()->route('verify-otp');
    }

    public function verifyOtp(Request $request): Response | RedirectResponse
    {
        $email = $request->session()->get('email');

        if (!$email) {
            return redirect()->route('register')->withErrors(['email' => 'Email not found in session.']);
        }

        // check if email is send and that not expired
        if (!EmailOtp::exists($email)) {
            EmailOtp::generateAndSend($email);
            return redirect()->route('verify-otp')->with('success', 'A new OTP has been sent to your email.');
        }

        $resendIn = EmailOtp::resendRemainingTime($email);

        return Inertia::render('auth/verify-otp', ['email' => $email, 'resendIn' => $resendIn]);
    }

    public function resendOtp(): RedirectResponse
    {
        $email = session('email');


        if (!$email) {
            return redirect()->route('register')->withErrors(['email' => 'Email not found in session.']);
        }

        if (EmailOtp::hasExceededOtpLimit($email)) {
            return redirect()->route('register')->with('error', 'You have reached the OTP request limit for today. Please try again after 24 hours.');
        }

        // Generate and send a new OTP
        if (EmailOtp::resend($email)) {
            return redirect()->route('verify-otp')->with('success', 'A new OTP has been sent to your email.');
        }

        $resendIn = EmailOtp::resendRemainingTime($email);

        return redirect()->route('verify-otp')->with('error', "You can only resend the OTP after {$resendIn} seconds.");
    }

    public function store(Request $request): RedirectResponse
    {

        $request->validate([
            'otp' => 'required|string|size:8',
        ]);

        $email = $request->session()->get('email');

        if (!$email) {
            return redirect()->route('register')->withErrors(['email' => 'Email not found in session.']);
        }


        if (EmailOtp::verify($email, $request->input('otp'))) {
            $user = User::create([
                'email' => $email,
                'password' => Hash::make('password'),
            ]);

            event(new Registered($user));

            Auth::login($user);

            return redirect()->intended(route('dashboard', absolute: false));
        }

        return redirect()->back()->withErrors(['otp' => 'Invalid or expired OTP.'])->withInput();
    }
}
