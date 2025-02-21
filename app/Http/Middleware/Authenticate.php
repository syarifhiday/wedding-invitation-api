<?php

namespace App\Http\Middleware;

use Illuminate\Http\JsonResponse;

class Authenticate
{
    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
    }

}
