import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';
import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import React, { useState } from 'react';

interface ProjectForm {
  name: string;
  members: { email: string; role: string }[];
}

const Project = () => {
  const { data, setData, post, processing, errors, setError, clearErrors } = useForm<Required<ProjectForm>>({
    name: '',
    members: [{ email: '', role: 'viewer' }],
  });

  // Separate state for member-specific errors
  const [memberErrors, setMemberErrors] = useState<string[]>(['']);

  const isValidEmail = (email: string) => {
    return email && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  };

  const canAddNewEmail = () => {
    return data.members.every((member) => isValidEmail(member.email));
  };

  const addEmailField = () => {
    if (canAddNewEmail()) {
      setData('members', [...data.members, { email: '', role: 'viewer' }]);
      setMemberErrors([...memberErrors, '']);
    }
  };

  const updateEmail = (index: number, value: string) => {
    const newMembers = [...data.members];
    newMembers[index] = { ...newMembers[index], email: value };
    setData('members', newMembers);

    // Validate email format
    const newErrors = [...memberErrors];
    if (value && !isValidEmail(value)) {
      newErrors[index] = 'Invalid email format';
    } else {
      newErrors[index] = '';
    }
    setMemberErrors(newErrors);
  };

  const updateRole = (index: number, value: string) => {
    const newMembers = [...data.members];
    newMembers[index] = { ...newMembers[index], role: value };
    setData('members', newMembers);
  };

  const removeEmailField = (index: number) => {
    if (data.members.length > 1) {
      const newMembers = data.members.filter((_, i) => i !== index);
      const newErrors = memberErrors.filter((_, i) => i !== index);
      setData('members', newMembers);
      setMemberErrors(newErrors);
    }
  };

  const submit = (e: React.FormEvent) => {
    e.preventDefault();

    // Validate all emails before submission
    let hasErrors = false;
    const newErrors = [...memberErrors];
    data.members.forEach((member, index) => {
      if (!member.email) {
        newErrors[index] = 'Email is required';
        hasErrors = true;
      } else if (!isValidEmail(member.email)) {
        newErrors[index] = 'Invalid email format';
        hasErrors = true;
      } else {
        newErrors[index] = '';
      }
    });
    setMemberErrors(newErrors);

    if (!data.name) {
      setError('name', 'Project name is required');
      hasErrors = true;
    } else {
      clearErrors('name');
    }

    if (!hasErrors) {
      post(route('startup.project.invite.store'));
    }
  };

  return (
    <AuthLayout title="Manage Your Project" description="Invite and manage your project members">
      <Head title="Project Management" />
      <form className="flex flex-col gap-6" onSubmit={submit}>
        {/* Project Name */}
        <div className="grid gap-2">
          <Label htmlFor="name">Project Name</Label>
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
            placeholder="Enter project name"
          />
          <InputError message={errors.name} className="mt-2" />
        </div>
        {/* Project Members */}
        <div className="grid gap-2">
          <Label>Project Members</Label>
          {data.members.map((member, index) => (
            <div key={index} className="flex items-center gap-2">
              <div className="flex-1 flex items-center gap-2">
                <div className="flex-1">
                  <Input
                    id={`member-email-${index}`}
                    type="email"
                    required
                    tabIndex={index * 2 + 2}
                    autoComplete="email"
                    value={member.email}
                    onChange={(e) => updateEmail(index, e.target.value)}
                    disabled={processing}
                    placeholder="Invite user email"
                  />
                  <InputError message={memberErrors[index]} className="mt-2" />
                </div>
                <div className="w-32">
                  <select
                    id={`member-role-${index}`}
                    value={member.role}
                    onChange={(e) => updateRole(index, e.target.value)}
                    disabled={processing}
                    className="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                    tabIndex={index * 2 + 3}
                  >
                    <option value="owner">Owner</option>
                    <option value="admin">Admin</option>
                    <option value="editor">Editor</option>
                    <option value="viewer">Viewer</option>
                    <option value="commenter">Commenter</option>
                  </select>
                </div>
              </div>
              {data.members.length > 1 && (
                <Button
                  type="button"
                  variant="destructive"
                  onClick={() => removeEmailField(index)}
                  disabled={processing}
                  className="h-10 w-10"
                >
                  -
                </Button>
              )}
            </div>
          ))}
          <Button
            type="button"
            variant="outline"
            onClick={addEmailField}
            disabled={processing || !canAddNewEmail()}
            className="mt-2 w-fit"
          >
            + Add Another Email
          </Button>
        </div>
        <Button type="submit" tabIndex={data.members.length * 2 + 2} disabled={processing}>
          {processing && <LoaderCircle className="h-4 w-4 animate-spin mr-2" />}
          Add Project Members
        </Button>
      </form>
    </AuthLayout>
  );
};

export default Project;