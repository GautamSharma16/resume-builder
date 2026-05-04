<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index()
    {
        $users = User::where('role', '!=', 'user')
            ->latest()
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = [
            'admin' => 'Administrator',
            'seo' => 'SEO Manager',
            'company' => 'Company Staff',
            'developer' => 'Developer',
            'article_writer' => 'Article Writer',
        ];

        $permissions = $this->getAvailablePermissions();

        return view('admin.users.create', compact('roles', 'permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:admin,seo,company,developer,article_writer'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'permissions' => $request->permissions,
            'mobile' => $request->mobile ?? '0000000000',
            'provider' => 'email',
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users.index')->with('status', 'Team member created successfully.');
    }

    public function edit(User $user)
    {
        if ($user->role === 'user') {
            abort(403, 'Normal users cannot be managed here.');
        }

        $roles = [
            'admin' => 'Administrator',
            'seo' => 'SEO Manager',
            'company' => 'Company Staff',
            'developer' => 'Developer',
            'article_writer' => 'Article Writer',
        ];

        $permissions = $this->getAvailablePermissions();

        return view('admin.users.edit', compact('user', 'roles', 'permissions'));
    }

    public function update(Request $request, User $user)
    {
        if ($user->role === 'user') {
            abort(403, 'Normal users cannot be managed here.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['required', 'in:admin,seo,company,developer,article_writer'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'permissions' => $request->permissions,
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Rules\Password::defaults()],
            ]);
            $user->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('admin.users.index')->with('status', 'Team member updated successfully.');
    }

    private function getAvailablePermissions()
    {
        return [
            'analytics' => 'View Analytics',
            'visits' => 'View Visits',
            'purchases' => 'View Purchases',
            'transactions' => 'View Transactions',
            'pricing' => 'Manage Pricing Plans',
            'templates' => 'Manage Templates',
            'articles' => 'Manage Articles',
            'team' => 'Manage Team Members',
        ];
    }

    public function destroy(User $user)
    {
        if ($user->role === 'user') {
            abort(403, 'Normal users cannot be managed here.');
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'Team member deleted.');
    }
}
