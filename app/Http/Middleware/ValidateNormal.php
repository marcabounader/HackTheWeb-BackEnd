<?php

namespace App\Http\Middleware;

use App\Http\Controllers\UnauthorizedController;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ValidateNormal
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user=Auth::user();
        if($user->type_id=="3"){
            return $next($request);
        }

        return redirect()->action([UnauthorizedController::class, "unauthorized"]);
    }
}
