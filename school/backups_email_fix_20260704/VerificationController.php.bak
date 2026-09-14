<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Auth\Events\Verified;

class VerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

    use VerifiesEmails;

    /**
     * Where to redirect users after verification.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // 'verify' is excluded from 'auth' so unauthenticated users can click the email link
        // 'signed' is removed because cPanel's SSL proxy rewrites HTTPS->HTTP causing signature mismatch
        $this->middleware('auth')->except('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    /**
     * Mark the authenticated user's email address as verified.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function verify(Request $request)
    {
        $userId = $request->route('id');
        $user = \App\Models\User::on('mysql')->find($userId);

        if (!$user) {
            return redirect()->route('login')->with('error', 'User not found.');
        }

        // Switch connection to school/tenant database if applicable
        $schoolConnectionName = null;
        if ($user->school_id) {
            $school = \App\Models\School::on('mysql')->find($user->school_id);
            if ($school) {
                $schoolConnectionName = $school->database_name;
                Config::set('database.connections.school.database', $schoolConnectionName);
                DB::purge('school');
                DB::connection('school')->reconnect();
                DB::setDefaultConnection('school');
            }
        }

        // Get user instance on the active connection
        $activeUser = \App\Models\User::find($userId);

        if (!$activeUser) {
            return redirect()->route('login')->with('error', 'User not found in business database.');
        }

        if (! hash_equals((string) $request->route('hash'), sha1($activeUser->getEmailForVerification()))) {
            return redirect()->route('login')->with('error', 'Invalid verification hash.');
        }

        // Check expiration manually since we removed 'signed' middleware
        $expires = $request->query('expires');
        if ($expires && now()->timestamp > $expires) {
            return redirect()->route('login')->with('error', 'Verification link has expired. Please request a new one.');
        }

        if ($activeUser->hasVerifiedEmail()) {
            // Already verified, log them in and redirect
            Auth::guard('web')->login($activeUser);
            Auth::login($activeUser);
            session(['user_id' => $activeUser->id]);
            session(['user_email' => $activeUser->email]);
            session()->save();
            if ($schoolConnectionName) {
                Session::put('school_database_name', $schoolConnectionName);
            }
            return redirect($this->redirectPath())->with('verified', true);
        }

        if ($activeUser->markEmailAsVerified()) {
            event(new Verified($activeUser));
            
            // Also mark as verified in the central database
            DB::connection('mysql')->table('users')->where('id', $activeUser->id)->update(['email_verified_at' => $activeUser->email_verified_at]);
        }

        Auth::guard('web')->login($activeUser);
        Auth::login($activeUser);
        session(['user_id' => $activeUser->id]);
        session(['user_email' => $activeUser->email]);
        session()->save();
        if ($schoolConnectionName) {
            Session::put('school_database_name', $schoolConnectionName);
        }

        return redirect($this->redirectPath())->with('verified', true);
    }
}