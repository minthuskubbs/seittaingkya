<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:users.manage');
    }

    public function index()
    {
        $users = User::with(['roles', 'clinic'])->latest()->paginate(20);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => ['required', Rule::in($this->assignableRoles())],
            // clinic required unless the new user is a super admin
            'clinic_id' => 'nullable|exists:clinics,id',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ]);

        // Super admins are not tied to a clinic; everyone else must have one.
        if ($data['role'] !== 'super_admin' && empty($data['clinic_id'])) {
            return back()->withErrors(['clinic_id' => 'Please select a clinic for this role.'])->withInput();
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'clinic_id' => $data['role'] === 'super_admin' ? null : $data['clinic_id'],
            'phone' => $data['phone'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);
        $user->syncRoles($data['role']);
        AuditLog::record('created', "Created user {$user->email} ({$data['role']})", $user);

        return redirect()->route('users.index')->with('status', 'User created.');
    }

    public function edit(User $user)
    {
        return view('users.edit', $this->formData() + ['user' => $user]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'role' => ['required', Rule::in($this->assignableRoles())],
            'clinic_id' => 'nullable|exists:clinics,id',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ]);

        if ($data['role'] !== 'super_admin' && empty($data['clinic_id'])) {
            return back()->withErrors(['clinic_id' => 'Please select a clinic for this role.'])->withInput();
        }

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'clinic_id' => $data['role'] === 'super_admin' ? null : $data['clinic_id'],
            'phone' => $data['phone'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);
        if (! empty($data['password'])) {
            $user->update(['password' => Hash::make($data['password'])]);
        }
        $user->syncRoles($data['role']);
        AuditLog::record('updated', "Updated user {$user->email}", $user);

        return redirect()->route('users.index')->with('status', 'User updated.');
    }

    public function destroy(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'You cannot delete yourself.');
        $user->delete();

        return redirect()->route('users.index')->with('status', 'User deleted.');
    }

    private function assignableRoles(): array
    {
        return ['super_admin', 'clinic_admin', 'assistance_admin'];
    }

    private function formData(): array
    {
        return [
            'clinics' => Clinic::orderBy('name')->get(),
            'roles' => $this->assignableRoles(),
        ];
    }
}
