<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendOtpMail;
use Illuminate\Support\Str;

class EmailOtp extends Model
{

    use HasUuids;

    protected $fillable = ['email', 'otp', 'expires_at', 'used'];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    const RESEND_TIMEOUT_SECONDS = 30;

    public static function generateAndSend(string $email): self
    {

        if (self::hasExceededOtpLimit($email)) {
            abort(429, 'You have reached the OTP request limit for today. Please try again later.');
        }

        // Invalidate old unused OTPs
        static::where('email', $email)
            ->where('used', false)
            ->where('expires_at', '>=', now())
            ->update(['used' => false]);

        $prefix = self::safeRandomString(2); // Only letters
        $suffix = self::safeRandomString(6); // Generate 6 alphanumeric characters

        // Combine OTP (e.g., AB89PW7U)
        $otp = $prefix . $suffix;

        // Store plain OTP (no dash) in DB
        $record = static::create([
            'email' => $email,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(10),
        ]);

        // Show formatted OTP in email (e.g., AB-89PW7U)
        $formattedOtp = substr($otp, 0, 2) . '-' . substr($otp, 2);

        // Send formatted OTP
        Mail::to($email)->send(new SendOtpMail($formattedOtp));

        return $record;
    }

    // ✅ Check if OTP can be resent
    public static function canResend(string $email): bool
    {
        return self::resendRemainingTime($email) === 0;
    }

    // ✅ Generate and send OTP
    public static function resend(string $email): ?self
    {
        if (!self::canResend($email)) {
            return null;
        }

        return self::generateAndSend($email);
    }

    // ✅ Verify OTP
    public static function verify(string $email, string $otp): bool
    {
        $record = static::where('email', $email)
            ->where('otp', $otp)
            ->where('used', false)
            ->where('expires_at', '>=', now())
            ->first();

        if (!$record) {
            return false;
        }

        $record->update([
            'used' => true,
            'used_at' => now(),
        ]);

        return true;
    }

    // ✅ Check if OTP exists and is valid
    public static function exists(string $email): bool
    {
        return static::where('email', $email)->where('used', false)->where('expires_at', '>=', now())->exists();
    }

    /**
     * Generate a safe random string using only unambiguous characters.
     */
    private static function safeRandomString(int $length): string
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // No O, 0, I, 1, l
        $otp = '';

        for ($i = 0; $i < $length; $i++) {
            $otp .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $otp;
    }

    // remaining time in seconds
    public static function resendRemainingTime(string $email): int
    {
        $latest = static::where('email', $email)->latest()->first();

        if (! $latest) {
            return 0; // No previous OTP sent — allow resend
        }

        $resendAfter = $latest->created_at->addSeconds(self::RESEND_TIMEOUT_SECONDS);
        $remaining = now()->diffInSeconds($resendAfter, false); // false → allow negative result

        return max(0, $remaining); // Never return negative
    }

    public static function hasExceededOtpLimit(string $email): bool
    {
        return static::where('email', $email)
            ->where('created_at', '>=', now()->subDay())
            ->where('used', false)
            ->count() >= 5;
    }
}
