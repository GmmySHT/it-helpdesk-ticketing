<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Show edit profile form.
     */
    public function edit(Request $request)
    {
        $user = $request->user();
        $departments = $this->getDepartments();
        return view('profile.edit', compact('user', 'departments'));
    }

    /**
     * Update profile (name, username, email, phone, department, and optional password).
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6|confirmed',
            'current_password' => 'nullable|required_with:password|current_password',
        ]);

        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->department = $request->department;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('profile.edit')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Show change password form.
     */
    public function editPassword()
    {
        return view('profile.change-password');
    }

    /**
     * Update password only.
     */
    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('profile.edit')
            ->with('success', 'Password berhasil diubah.');
    }

    /**
     * Delete account - require password confirmation.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => 'required|string'
        ]);

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->with('error', 'Password salah.');
        }

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Akun berhasil dihapus.');
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
