import React from 'react'
import AuthLayout from '@/layouts/auth-layout'
import { Head, useForm } from '@inertiajs/react'
import { Button } from '@/components/ui/button'
import { LoaderCircle } from 'lucide-react'
import InputError from '@/components/input-error'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'


type PasswordLoginProps = {
  email: string;
};

const PasswordLogin: React.FC<PasswordLoginProps> = ({ email }) => {

  const { data, setData, post, processing, errors } = useForm<{ password: string }>({
    password: '',
  });


  const passwordLogin = (e: React.FormEvent) => {
    e.preventDefault()
    post(route('password.login.store'));
  }

  return (
    <AuthLayout title="Verify OTP" description="Enter your OTP below to verify your account">
      <Head title="Register" />
      <form className="flex flex-col gap-6" onSubmit={passwordLogin}>
        <div className="grid gap-2">
          <Label htmlFor="email">Email address</Label>
          <Input
            id="email"
            type="email"
            required
            tabIndex={2}
            autoComplete="email"
            value={email}
            disabled
            placeholder="email@example.com"
            readOnly
          />
        </div>
        <div className="grid gap-2">
          <Label htmlFor="password">Password</Label>
          <Input
            id="password"
            type="password"
            required
            tabIndex={3}
            autoComplete="new-password"
            value={data.password}
            onChange={(e) => setData('password', e.target.value)}
            disabled={processing}
            placeholder="Password"
          />
          <InputError message={errors.password} />
        </div>
        <div>
          <Button type="submit" className="mt-2 w-full" tabIndex={5} disabled={processing}>
            {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
            Login
          </Button>
        </div>
      </form>
    </AuthLayout >
  )
}

export default PasswordLogin