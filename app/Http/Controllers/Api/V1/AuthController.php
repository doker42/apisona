<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\AuthRefreshRequest;
use App\Http\Requests\V1\LoginRequest;
use App\Http\Requests\V1\RegisterRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use App\Passport\Traits\AuthPassportTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class AuthController extends Controller
{
    use AuthPassportTrait;

    /**
     * @param RegisterRequest $request
     * @return JsonResponse
     * @throws \App\Passport\ClientAbsentException
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $input = $request->validated();

        $input['password'] = bcrypt($input['password']);
        /** @var User $user */
        $user = User::create($input);

        $result = self::getToken(User::PASSPORT_CLIENT_NAME, $input['email'], $user->password);

        if (!$result->error) {

            return response()->json([
                'user' => new UserResource($user),
                'authorization' => $result->token
            ]);
        }
        else {

            return response()->json([
                'error' => $result->error,
                'error_message' => $result->error_message
            ], 401);
        }
    }


    /**
     * @param LoginRequest $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        try {

            $remember = $request->input('remember');

            if (Auth::attempt($credentials, $remember)) {

                $user = Auth::user();

                if ($user) {

                    $result = self::getToken(User::PASSPORT_CLIENT_NAME, $credentials['email'], $credentials['password'], $remember);

                    if (!$result->error) {

                        return response()->json([
                            'user' => new UserResource($user),
                            'authorization' => $result->token
                        ]);
                    }
                    else {

                        return response()->json([
                            'error' => $result->error,
                            'error_message' => $result->error_message
                        ], 401);
                    }
                }
            }

        } catch (\Exception $e) {

            Log::info('LOGIN Error message: ' . $e->getMessage());

            return response()->json([
                'message' => __('auth.token.cant')
            ], 422);
        }

        $request->authenticate(true);

        return response()->json(['message' => trans('auth.failed')], 422);
    }


    /**
     * @param AuthRefreshRequest $request
     * @return JsonResponse
     * @throws \App\Passport\ClientAbsentException
     */
    public function refresh(AuthRefreshRequest $request): JsonResponse
    {
        $result = self::getRefreshToken(User::PASSPORT_CLIENT_NAME, $request->validated('refresh_token'));

        if ($result->error) {
            return response()->json([
                'error' => $result->error,
                'error_message' => $result->error_message
            ], 401);
        }

        return response()->json([
            'authorization' => $result->token
        ]);
    }


    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $user->token()->revoke();
        return response()
            ->json([
                'message' => __("Successfully logged out"),
            ]);
    }
}
