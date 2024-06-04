<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\PasswordUpdateRequest;
use App\Http\Resources\V1\UserResource;
use App\Http\Traits\ApiHelperTrait;
use App\Mail\ResetPasswordNotUserMail;
use App\Models\EmailSet;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    use ApiHelperTrait;

    /**
     * @return JsonResponse
     */
    public function show(): JsonResponse
    {
        $user = Auth::user();

        if ($user) {

            return response()->json([
                'user' => new UserResource($user),
                'locales' => Helper::locales(),
            ], 200);
        }

        return response()->json([
            'message' => __("User hasn't been found")
        ], 404);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function setEmail(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
        ]);

        $emailSet = EmailSet::where('token', $request->token)->firstOrFail();

        if (!$emailSet) {
            return response()->json([
                'message' => __('Failed to change email.')
            ], 404);
        }

        if ($emailSet->isExpired()) {
            $emailSet->delete();
            return response()->json([
                'message' => __('Link for change email was expired.')
            ], 401);
        }

        $emailSet->entity()->update(['email' => $emailSet->email]);
        $emailSet->delete();

        return response()->json([
            'message' => __('Your email has been changed.'),
        ]);
    }


    /**
     * @param PasswordUpdateRequest $request
     * @return JsonResponse
     */
    public function updatePassword(PasswordUpdateRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        if ($user->update(['password' => Hash::make($request->new_password)])) {

            return response()->json([
                'message' => __('Your password has been successfully updated.'),
            ], 200);
        }

        return response()->json([
            'message' => __('Failed to change your password. Please, try again.'),
        ], 500);
    }



    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email'  => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        $response = [
            'sent' => [
                'message' => __('Further instructions have been sent to provided email address.')
            ],
            'throttle' => [
                'message' => __('The request has already been made. Try again in 10 minutes.')
            ]
        ];

        /** if user exists */
        if ($status == Password::RESET_LINK_SENT) {
            return response()->json($response['sent'], 200);
         /** if the user does not exist */
        } elseif ($status == Password::INVALID_USER) {
            Mail::toWithBcc($request->email)->send(new ResetPasswordNotUserMail());
            return response()->json($response['sent'], 200);
        } elseif ($status == Password::RESET_THROTTLED) {
            return response()->json($response['throttle'], 429);
        }

        return response()->json([
            'message' => __('Failed sending reset link.')
        ], 400);
    }



    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'token'    => 'required',
            'password' => \Illuminate\Validation\Rules\Password::min(8)->letters()->numbers(),
        ]);


        $is_token = DB::table('password_resets')->where(['email'=> $request->email])->first();

        if (!$is_token) {
            return response()->json([
                'message'=> __('Token has expired. Please try repair your password again.')
            ], 403);
        }


        $status = Password::reset(
            $request->only('email', 'token', 'password'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        if ($status == Password::PASSWORD_RESET) {

            User::removeInviteToken($request->email);

            return response()->json([
                'message'=> __('Your password has been reset!'),
            ], 200);
        }

        return response()->json([
            'message'=> __('Failed sending reset link.')
        ], 500);
    }



    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function setPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => \Illuminate\Validation\Rules\Password::min(8)->letters()->numbers(),
        ]);

        $updatePassword = DB::table('password_reset_tokens')->where([
            'email' => $request->email,
            'token' => $request->token
        ])->first();

        if (!$updatePassword) {
            return response()->json([
                'message'=> __('Failed reset password.')
            ], 500);
        }

        if (User::expired($updatePassword->created_at)) {
            return response()->json([
                'message'=> __('Link for set password was expired.')
            ], 401);
        }

        User::where('email', $request->email)->update(['password' => Hash::make($request->password)]);

        User::removeInviteToken($request->email);

        return response()->json([
            'message'=> __('Your password has been reset!'),
        ], 200);
    }

}
