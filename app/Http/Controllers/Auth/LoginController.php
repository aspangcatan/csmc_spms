<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('username', $credentials['username'])->first();

        // In many legacy systems, passwords might be MD5 or plain. 
        // Here we check for plain text as a fallback if standard Laravel hashing isn't used.
        if ($user && ($credentials['password'] === $user->password || Hash::check($credentials['password'], $user->password))) {
            Auth::login($user);
            $request->session()->regenerate();
            return redirect()->intended('/ipcr');
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:4|confirmed',
        ]);

        $user = Auth::user();

        // Check current password (supporting both hashed and plain text for legacy)
        $isCorrect = false;
        if ($user->password === $request->current_password || Hash::check($request->current_password, $user->password)) {
            $isCorrect = true;
        }

        if (!$isCorrect) {
            return response()->json(['message' => 'The provided current password does not match our records.'], 422);
        }

        // Update password with hashing
        $newHashedPassword = Hash::make($request->new_password);
        
        try {
            // Update the 'user' connection specifically
            DB::connection('user')
                ->table('users')
                ->where('id', $user->id)
                ->update(['password' => $newHashedPassword]);
                
            return response()->json(['message' => 'Password updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update password: ' . $e->getMessage()], 500);
        }
    }
}
