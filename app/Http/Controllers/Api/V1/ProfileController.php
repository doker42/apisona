<?php

namespace App\Http\Controllers\Api\V1;

use App\Common\AvatarManager;
use App\Common\StorageLocalPublic;
use App\Events\UserEmailUpdateEvent;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\AvatarUpdateRequest;
use App\Http\Requests\V1\PasswordUpdateRequest;
use App\Http\Requests\V1\UserUpdateRequest;
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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    use ApiHelperTrait;

    public AvatarManager $manager;


    public function __construct(StorageLocalPublic $storage)
    {
        $this->manager = new AvatarManager($storage);
    }

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
     * @param UserUpdateRequest $request
     * @return JsonResponse
     */
    public function update(UserUpdateRequest $request)
    {
        /** @var User $user */
        $user = auth()->user();
        $updated_user = $request->validated();

        DB::beginTransaction();

        try {

            if (!empty($updated_user['email'])) {
                if ($user->email !== $updated_user['email']) {
                    $emailSet = EmailSet::addRequest($user, $updated_user['email']);
                    if ($emailSet) {
                        event(new UserEmailUpdateEvent($user, $emailSet));
                    }
                    else{
                        $warnings['email'] = __('You already requested change email to :email. Please approve this request or try again later.', ['email' => $updated_user['email']]);
                    }
                    unset($updated_user['email']);
                }
            }

            if (array_key_exists('new_password', $updated_user) && !is_null($updated_user['new_password'])) {
                $updated_user['password'] = Hash::make($request->new_password);
            }

            $user->update($updated_user);

            DB::commit();

            $response = [
                'user' => new UserResource($user),
                'locales' => Helper::locales($user->language),
                'message' => __('Your data has been successfully updated.'),
            ];

            if(!empty($warnings)){
                $response['warnings'] = $warnings;
            }

            return response()->json($response);

        } catch (\Exception $e) {
            DB::rollback();
            Log::info('User update profile Error : ' . $e->getMessage());
        }

        return response()->json([
            'message' => __('Failed to change your data. Please, try again.')
        ], 500);
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



    /**
     * @return JsonResponse
     */
    public function showAvatar()
    {
        $user = auth()->user();

        return response()->json([
            'avatar' => $this->manager->getThumbnails($user->avatar, array_keys(config('images.profile.avatar')))
        ], 200);
    }


    /**
     * @param AvatarUpdateRequest $request
     * @return JsonResponse
     */
    public function updateAvatar(AvatarUpdateRequest $request): JsonResponse
    {
        $user = auth()->user();
        $avatar_name = null;
        $old_avatar_name = $user->avatar;

        if ($request->avatar) {
            /* store new avatars */
            $avatar_name = $this->manager->store($request->avatar, config('images.profile.avatar'));
        }

        if ($avatar_name && $user->update(['avatar' => $avatar_name])) {
            /* remove old avatars */
            if ($old_avatar_name) {
                $this->manager->deleteAll($old_avatar_name, AvatarManager::DIR_AVATARS, config('images.profile.avatar'));
            }

            return response()->json([
                'message' => __('Avatar has been successfully updated.'),
                'avatar' => $this->manager->getThumbnails($user->avatar, array_keys(config('images.profile.avatar')))

            ], 200);
        }

        return response()->json([
            'message' => __('Failed to update avatar.')
        ], 500);
    }


    /**
     * @return JsonResponse
     */
    public function deleteAvatar(): JsonResponse
    {
        $user = auth()->user();

        $avatar = $user?->avatar;

        if ($avatar) {
            $this->manager->deleteAll($user->avatar, AvatarManager::DIR_AVATARS, config('images.profile.avatar'));


            $user->update(['avatar' => null]);

            return response()->json([
                'message' => __('Your avatar has been successfully deleted.'),
            ], 200);
        }
        else {

            return response()->json([
                'message' => __('No avatar.'),
            ], 422);
        }
    }

}
