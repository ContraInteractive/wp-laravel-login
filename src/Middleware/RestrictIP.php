<?php
namespace ContraInteractive\WpLaravelLogin\Middleware;

use Closure;
use Illuminate\Http\Request;

class RestrictIp
{

    protected $allowedIps = []; // Example allowed IP list

    public function __construct()
    {
        $this->allowedIps = config('wp-login.sync_password_allowed_ips');
    }


    public function handle(Request $request, Closure $next)
    {
        $clientIp = $request->ip();

        if (!in_array($clientIp, $this->allowedIps)) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        return $next($request);

    }
}