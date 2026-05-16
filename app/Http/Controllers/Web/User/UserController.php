<?php


namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\EnumConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function create(Request $request): View
    {
        $this->authorize('create', User::class);

        $assignableValues = EnumConfig::userRoleAssignableRoles($request->user()->role);
        $assignableRoles  = array_filter(
            EnumConfig::options('user_role'),
            fn ($opt) => in_array($opt->value, $assignableValues, true),
        );

        return view('users.create', compact('assignableRoles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $assignableValues = EnumConfig::userRoleAssignableRoles($request->user()->role);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => ['required', Rule::in($assignableValues)],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'],
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        return redirect()
            ->route('tenant.projects.index')
            ->with('success', "User \"{$user->name}\" created successfully.");
    }

    public function profile(Request $request): View
    {
        return view('users.profile', ['user' => $request->user()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $this->authorize('updateOwnProfile', $user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'current_password' => ['nullable', 'string', 'required_with:password'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        if (isset($validated['current_password'])) {
            if (! Hash::check($validated['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'The current password is incorrect.']);
            }
        }

        $user->name = $validated['name'];

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
            $user->password_changed_at = now();
        }

        $user->save();

        return back()->with('success', 'Profile updated successfully.');
    }
}
