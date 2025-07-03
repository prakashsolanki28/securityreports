import React, { useEffect } from 'react'
import {
  InputOTP,
  InputOTPGroup,
  InputOTPSeparator,
  InputOTPSlot,
} from "@/components/ui/input-otp"
import { REGEXP_ONLY_DIGITS_AND_CHARS } from "input-otp"

import AuthLayout from '@/layouts/auth-layout'
import { Head, useForm } from '@inertiajs/react'
import { Button } from '@/components/ui/button'
import { LoaderCircle } from 'lucide-react'
import InputError from '@/components/input-error'
import TextLink from '@/components/text-link'


interface VerifyOtpProps {
  resendIn: number; // Time in seconds to wait before resending OTP
}

const VerifyOtp = ({ resendIn }: VerifyOtpProps) => {

  const [remainingTime, setRemainingTime] = React.useState(resendIn);
  const { data, setData, post, processing, errors } = useForm<{ otp: string }>({
    otp: '',
  });


  const verifyOtp = (e: React.FormEvent) => {
    e.preventDefault()
    post(route('verify-otp.store'));
  }

  useEffect(() => {
    if (remainingTime > 0) {
      const timer = setInterval(() => {
        setRemainingTime(prev => Math.max(0, prev - 1));
      }, 1000);
      return () => clearInterval(timer);
    }
  }, [remainingTime]);

  return (
    <AuthLayout title="Verify OTP" description="Enter your OTP below to verify your account">
      <Head title="Register" />
      <form className="flex flex-col gap-6" onSubmit={verifyOtp}>
        <div className="grid gap-2">
          <div className='flex items-center justify-center gap-2'>
            <InputOTP maxLength={8} pattern={REGEXP_ONLY_DIGITS_AND_CHARS} value={data?.otp} onChange={(value) => setData('otp', value)} autoFocus>
              <InputOTPGroup className="w-full">
                <InputOTPSlot index={0} className='w-9' />
                <InputOTPSlot index={1} className='w-9' />
              </InputOTPGroup>
              <InputOTPSeparator />
              <InputOTPGroup className="w-full">
                <InputOTPSlot index={2} className='w-10' />
                <InputOTPSlot index={3} className='w-10' />
                <InputOTPSlot index={4} className='w-10' />
                <InputOTPSlot index={5} className='w-10' />
                <InputOTPSlot index={6} className='w-10' />
                <InputOTPSlot index={7} className='w-10' />
              </InputOTPGroup>
            </InputOTP>
          </div>
          <InputError message={errors.otp} className="mt-2" />
        </div>
        <div>
          <Button type="submit" className="mt-2 w-full" tabIndex={5} disabled={processing}>
            {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
            Verify OTP
          </Button>
        </div>
        {remainingTime > 0 && (
          <div className="text-center text-sm text-muted-foreground">
            You can resend the OTP in {remainingTime} seconds.
          </div>
        )}
        {remainingTime === 0 && (
          <div className="text-center text-sm text-muted-foreground">
            Didn't receive the OTP?&nbsp;&nbsp;<TextLink href={'resend-otp'} className="text-primary hover:underline">Resend</TextLink>
          </div>
        )}
      </form>
    </AuthLayout >
  )
}

export default VerifyOtp