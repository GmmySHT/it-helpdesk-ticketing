<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(20);
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = [
            'user' => 'User',
            'it_staff' => 'Tim IT',
            'admin' => 'Admin'
        ];

        $departments = $this->getDepartments();

        return view('users.create', compact('roles', 'departments'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:user,it_staff,admin',
            'password' => 'required|string|min:6|confirmed',
            'department' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password),
            'department' => $request->department,
            'phone' => $request->phone,
            'email_verified_at' => $request->has('verify_email') ? now() : null,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dibuat.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $roles = [
            'user' => 'User',
            'it_staff' => 'Tim IT',
            'admin' => 'Admin'
        ];

        $departments = $this->getDepartments();

        return view('users.edit', compact('user', 'roles', 'departments'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => "required|string|max:255|unique:users,username,{$user->id}",
            'email' => "required|email|unique:users,email,{$user->id}",
            'role' => 'required|in:user,it_staff,admin',
            'password' => 'nullable|string|min:6|confirmed',
            'department' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
        ]);

        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->department = $request->department;
        $user->phone = $request->phone;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->has('verify_email') && !$user->email_verified_at) {
            $user->email_verified_at = now();
        }

        $user->save();

        return redirect()->route('users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        // prevent deleting self accidentally
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        // prevent deleting last admin
        $adminCount = User::where('role', 'admin')->count();
        if ($user->role === 'admin' && $adminCount <= 1) {
            return back()->with('error', 'Tidak dapat menghapus admin terakhir.');
        }

        $user->delete();
        return back()->with('success', 'User berhasil dihapus.');
    }

    // ==================== ADMIN VERIFICATION METHODS ====================

    /**
     * Verify user email manually (by admin).
     */
    public function verifyEmail(User $user)
    {
        if (!$user->email_verified_at) {
            $user->email_verified_at = now();
            $user->save();
            return back()->with('success', 'Email user berhasil diverifikasi.');
        }

        return back()->with('info', 'Email user sudah diverifikasi sebelumnya.');
    }

    /**
     * Unverify user email (by admin).
     */
    public function unverifyEmail(User $user)
    {
        if ($user->email_verified_at) {
            $user->email_verified_at = null;
            $user->save();
            return back()->with('success', 'Verifikasi email user dibatalkan.');
        }

        return back()->with('info', 'Email user belum diverifikasi.');
    }

    // ==================== HELPER METHODS ====================

    /**
     * Get list of departments.
     */
    private function getDepartments()
    {
        return [
            'Information Technology' => 'Information Technology',
            'Human Resources' => 'Human Resources',
            'Finance & Accounting' => 'Finance & Accounting',
            'Marketing' => 'Marketing',
            'Sales' => 'Sales',
            'Operations' => 'Operations',
            'Customer Service' => 'Customer Service',
            'Legal' => 'Legal',
            'Research & Development' => 'Research & Development',
            'Procurement' => 'Procurement',
            'Logistics' => 'Logistics',
            'Quality Assurance' => 'Quality Assurance',
        ];
    }
}
