<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SwitchDatabase
{
    /**
     * Dynamically switches connection config to the specified school database.
     * Only purges connection if config database name actually changes.
     */
    public static function switchToSchool($databaseName)
    {
        if (!$databaseName) {
            return;
        }

        if (Config::get('database.connections.school.database') !== $databaseName) {
            Config::set('database.connections.school.database', $databaseName);
            DB::purge('school');
        }

        if (DB::getDefaultConnection() !== 'school') {
            DB::setDefaultConnection('school');
        }

        try {
            DB::connection('school')->getPdo();
        } catch (\Throwable $e) {
            Config::set('database.connections.school.database', null);
            DB::purge('school');
            DB::setDefaultConnection('mysql');

            Session::forget('school_database_name');
            Session::put('school_database_name', null);

            foreach (Session::all() as $key => $value) {
                if (strpos($key, 'login_') === 0 || strpos($key, 'password_hash_') === 0) {
                    Session::forget($key);
                }
            }
            Session::save();

            try {
                if (class_exists(\Illuminate\Support\Facades\Cookie::class)) {
                    \Illuminate\Support\Facades\Cookie::queue(\Illuminate\Support\Facades\Cookie::forget('remember_web'));
                    \Illuminate\Support\Facades\Cookie::queue(\Illuminate\Support\Facades\Cookie::forget('remember_school_web'));
                }
            } catch (\Throwable $ex) {}
        }
    }

    /**
     * Switches default connection back to the main mysql database.
     */
    public static function switchToMysql()
    {
        if (DB::getDefaultConnection() !== 'mysql') {
            DB::setDefaultConnection('mysql');
        }
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $school_database_name = Session::get('school_database_name');
        if ($school_database_name) {
            self::switchToSchool($school_database_name);
            if (Auth::user()) {
                return $next($request);
            }
            return redirect()->back()->with('error','Invalid credential.');
        } else {
            self::switchToMysql();
        }

        return $next($request);
    }
}
