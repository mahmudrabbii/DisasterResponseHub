<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\People;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    private function ensureVolunteerRecord(People $person): void
    {
        $user = User::where('person_id', $person->id)->first();

        if (!$user || $user->role !== 'volunteer') {
            return;
        }

        DB::table('volunteers')->updateOrInsert(
            ['person_id' => $person->id],
            [
                'skills' => DB::table('volunteers')->where('person_id', $person->id)->value('skills'),
                'availability' => DB::table('volunteers')->where('person_id', $person->id)->value('availability') ?? 'available',
            ]
        );
    }

    private function redirectAfterAuthentication(User $user, string $message)
    {
        if ($user->role === 'admin') {
            $targetRoute = 'admin.dashboard';
        } elseif ($user->role === 'official') {
            $targetRoute = 'official.dashboard';
        } elseif ($user->role === 'volunteer') {
            $targetRoute = 'volunteer.dashboard';
        } elseif ($user->role === 'public') {
            $targetRoute = 'public.home';
        } else {
            $targetRoute = 'dashboard';
        }

        return redirect()->route($targetRoute)->with('status', $message);
    }

    /**
     * Show the registration form.
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle a registration request.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:people,email'],
            'phone' => ['required', 'string', 'max:20'],
            'role' => ['required', Rule::in(['public', 'admin', 'official', 'volunteer'])],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Create person record
        DB::table('people')->insert([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'created_at' => now(),
        ]);

        // Get the person ID
        $person = People::where('email', $validated['email'])->first();

        // Create user record
        DB::table('users')->insert([
            'person_id' => $person->id,
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'created_at' => now(),
        ]);

        if ($validated['role'] === 'volunteer') {
            $this->ensureVolunteerRecord($person);
        }

        $user = User::where('person_id', $person->id)->first();

        Auth::login($user);
        $request->session()->regenerate();

        return $this->redirectAfterAuthentication($user, 'Registration successful. Welcome!');
    }

    /**
     * Show the login form.
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle an authentication request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'role' => ['required', Rule::in(['public', 'admin', 'official', 'volunteer'])],
            'password' => ['required', 'string'],
        ]);

        // Find person by email
        $person = People::where('email', $credentials['email'])->first();
        
        if (!$person) {
            return back()
                ->withErrors(['email' => 'The provided credentials do not match our records.'])
                ->onlyInput('email', 'role');
        }

        // Find user linked to this person
        $user = User::where('person_id', $person->id)->first();

        if (!$user || $user->role !== $credentials['role'] || !Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withErrors(['email' => 'The provided credentials do not match our records.'])
            ->onlyInput('email', 'role');
        }

        Auth::login($user);
        $request->session()->regenerate();

        if ($user->role === 'volunteer') {
            $this->ensureVolunteerRecord($person);
        }

        return $this->redirectAfterAuthentication($user, 'Login successful.');
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'You have been logged out.');
    }
}
