<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;
use App\Models\EmailOtp;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class AccountController extends Controller
{
    /**
     * Display the view page
     */
    public function account()
    {
        return Inertia::render('auth/account');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function sendOtp(Request $request): RedirectResponse | Response
    {
        $request->validate([
            'email' => 'required|string|lowercase|email|max:255',
        ]);

        $email = $request->email;
        $request->session()->put('email', $email);

        // check if password login is enabled
        $user = User::where('email', $email)->first();
        if ($user && $user->is_pwd_changed) {
            return redirect()->route('password.login');
        }

        // Generate/send OTP
        if (EmailOtp::hasExceededOtpLimit($email)) {
            return redirect()->route('login')->withErrors(['email' => 'You have reached the OTP request limit for today. Please try again after 24 hours.']);
        }

        EmailOtp::generateAndSend($email);

        return redirect()->route('verify-otp');
    }

    public function verifyOtp(Request $request): Response | RedirectResponse
    {
        $email = $request->session()->get('email');

        if (!$email) {
            return redirect()->route('login')->withErrors(['email' => 'Email not found in session.']);
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
            return redirect()->route('login')->withErrors(['email' => 'Something went wrong, please try again later.']);
        }

        if (EmailOtp::hasExceededOtpLimit($email)) {
            return redirect()->route('login')->with('error', 'You have reached the OTP request limit for today. Please try again after 24 hours.');
        }

        // Generate and send a new OTP
        if (EmailOtp::resend($email)) {
            return redirect()->route('verify-otp')->with('success', 'A new OTP has been sent to your email.');
        }

        $resendIn = EmailOtp::resendRemainingTime($email);

        return redirect()->route('verify-otp')->with('error', "You can only resend the OTP after {$resendIn} seconds.");
    }


    public function accountStore(Request $request): RedirectResponse
    {

        $request->validate([
            'otp' => 'required|string|size:8',
        ]);

        $email = $request->session()->get('email');

        if (!$email) {
            return redirect()->route('login')->withErrors(['email' => 'Email not found in session.']);
        }


        if (EmailOtp::verify($email, $request->input('otp'))) {
            $user = User::where('email', $email)->first();
            if (!$user) {
                $user = User::create([
                    'email' => $email,
                    'password' => Hash::make('password'),
                ]);
            }
            event(new Registered($user));
            Auth::login($user);
            return redirect()->intended(route('dashboard', absolute: false));
        }

        return redirect()->back()->withErrors(['otp' => 'Invalid or expired OTP.'])->withInput();
    }

    // passwordLogin
    public function passwordLogin(): Response | RedirectResponse
    {
        $email = session('email');
        if (!$email) {
            return redirect()->route('login')->withErrors(['email' => 'Email not found in session.']);
        }
        return Inertia::render('auth/password-login',[
            'email' => $email,
        ]);
    }

    // passwordLoginStore
    public function passwordLoginStore(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|string|min:8',
        ]);

        $email = session('email');
        if (!$email) {
            return redirect()->route('login')->withErrors(['email' => 'Email not found in session.']);
        }

        // check if password login is enabled
        $user = User::where('email', $email)->first();
        if (!$user) {
            return redirect()->route('login')->withErrors(['email' => 'User not found.']);
        }

        if (!Hash::check($request->input('password'), $user->password)) {
            return redirect()->back()->withErrors(['password' => 'Invalid password.'])->withInput();
        }

        Auth::login($user);
        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
