<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Controllers\GeneralSettings;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PermissionAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$param)
    {

        // Check if the user is authenticated
        $user = Auth::user();
        if (!$user) {
            // If user is not authenticated, redirect to login or any appropriate route
            return redirect()->route('login');
        }
        if (isset($param[1]) && $param[1]  == 'permission' && $user->roles !== "Super Admin") {
            return redirect()->route("access-denied");
        }

        // Check if the user is a "Super Admin"
        if ($user->roles == "Super Admin") {
            return $next($request);
        } else {
            $sett = new GeneralSettings();

            $ips = $sett->allowed_ip();

            $restrictIp = $sett->get_option("ip_restricted", true);

            // Check if IP restriction is enabled and if the user's IP is allowed
            if (is_array($ips) && $restrictIp == 1) {
                if (!in_array($request->ip(), $ips)) {
                    Auth::logout();
                    return redirect()
                        ->route("access-denied")
                        ->with(
                            "message",
                            "Your IP( " . $request->getClientIp() . " ) is Unknown, Please Contact With System Admin."
                        );
                }
            }

            // Check user permissions
            $permission = $user->permission;
            $stepExe = $permission;
            $n = 0;
            foreach ($param as $step) {
                if (isset($stepExe->$step)) {
                    $stepExe = $stepExe->$step;
                    $n++;
                }
            }

            // If all permissions are satisfied, allow the request to proceed
            if ($n == count($param)) {
                return $next($request);
            } else {
                return redirect()->route("access-denied");
            }
        }
    }
}
