import AuthLayout from '@/layouts/auth-layout'
import { Head } from '@inertiajs/react'
import React from 'react'

const Personal = () => {
  return (
    <AuthLayout title="Personal Information" description="Update your personal information">
      <Head title="Personal Information" />
      <form className="flex flex-col gap-6">
      </form>
    </AuthLayout>
  )
}

export default Personal