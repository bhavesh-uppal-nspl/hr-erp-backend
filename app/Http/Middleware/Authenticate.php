<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        // For APIs, return a JSON response instead of redirecting
        if (!$request->expectsJson()) {
            // Optional: Return a JSON error instead of redirect
            abort(response()->json([
                'message' => 'Unauthenticated.'
            ], 401));
        }
    }
}
