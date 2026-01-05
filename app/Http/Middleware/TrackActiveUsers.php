<?php

namespace App\Http\Middleware;

use App\Services\ActiveUserService;
use Closure;
use Illuminate\Http\Request;

class TrackActiveUsers
{
    public function __construct(protected ActiveUserService $activeUsers)
    {
    }

    /**
     * Store/update the current session activity timestamp.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($request->hasSession()) {
            $sessionId = $request->session()->getId();
            if ($sessionId) {
                $this->activeUsers->touch($sessionId);
            }
        }

        return $response;
    }
}
