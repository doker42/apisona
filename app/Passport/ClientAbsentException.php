<?php

namespace App\Passport;

use Illuminate\Http\JsonResponse;

class ClientAbsentException extends \Exception
{
    public function render($request): JsonResponse
    {
        return response()->json(['message' => __("Passport auth error!")], 403);
    }
}
