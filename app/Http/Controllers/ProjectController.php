<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Str;


class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function inviteStartUpProjectMember(): Response
    {
        return Inertia::render('settings/info/project');
    }

    public function inviteStartUpProjectMemberSave(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'members' => 'required|array',
            'members.*.email' => 'required|email|distinct',
            'members.*.role' => 'required|in:owner,admin,editor,viewer,commenter',
        ]);

        // Create or find the project
        $project = Project::firstOrCreate(
            ['name' => $request->name],
            ['status' => 'pending']
        );

        // Ensure the creator is also assigned as owner (if not already in list)
        $project->users()->syncWithoutDetaching([
            Auth::id() => [
                'role' => 'owner',
                'assigned_at' => now(),
            ],
        ]);

        // Invite each member
        foreach ($request->members as $memberData) {
            $user = User::firstOrCreate(
                ['email' => $memberData['email']],
                [
                    'name' => 'Pending User',
                    'password' => bcrypt(Str::random(16)),
                ]
            );

            $project->users()->syncWithoutDetaching([
                $user->id => [
                    'role' => $memberData['role'],
                    'assigned_at' => now(),
                ],
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Project members invited successfully.');
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
