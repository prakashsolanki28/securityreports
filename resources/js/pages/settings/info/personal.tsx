import InputError from '@/components/input-error'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import AuthLayout from '@/layouts/auth-layout'
import { Head, useForm } from '@inertiajs/react'
import { LoaderCircle } from 'lucide-react'
import React, { FormEventHandler } from 'react'

type RegisterForm = {
  name: string;
  designation: string;
  experience?: string;
};

const Personal = () => {

  const { data, setData, post, processing, errors } = useForm<Required<RegisterForm>>({
    name: '',
    designation: '',
    experience: '',
  });

  const submit: FormEventHandler = (e) => {
    e.preventDefault();
    post(route('profile.complete.store'));
  };

  return (
    <AuthLayout title="Personal Information" description="Update your personal information">
      <Head title="Personal Information" />
      <form className="flex flex-col gap-6" onSubmit={submit}>
        <div className="grid gap-2">
          <Label htmlFor="name">Name</Label>
          <Input
            id="name"
            type="text"
            required
            autoFocus
            tabIndex={1}
            autoComplete="name"
            value={data.name}
            onChange={(e) => setData('name', e.target.value)}
            disabled={processing}
            placeholder="Full name"
          />
          <InputError message={errors.name} className="mt-2" />
        </div>
        {/* designation */}
        <div className="grid gap-2">
          <Label htmlFor="designation">Designation</Label>
          <Input
            id="designation"
            type="text"
            required
            tabIndex={2}
            autoComplete="off"
            value={data.designation}
            onChange={(e) => setData('designation', e.target.value)}
            disabled={processing}
            placeholder="Your designation"
          />
          <InputError message={errors.designation} className="mt-2" />
        </div>
        {/* experience */}
        <div className="grid gap-2">
          <Label htmlFor="experience">Experience</Label>
          <Input
            id="experience"
            type="text"
            tabIndex={3}
            autoComplete="off"
            value={data.experience}
            onChange={(e) => setData('experience', e.target.value)}
            disabled={processing}
            placeholder="Years of experience"
          />
          <InputError message={errors.experience} className="mt-2" />
        </div>
        <div className="flex items-center justify-end mt-4">
          <Button type="submit" tabIndex={5} disabled={processing}>
            {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
            Next
          </Button>
        </div>
      </form>
    </AuthLayout>
  )
}

export default Personal