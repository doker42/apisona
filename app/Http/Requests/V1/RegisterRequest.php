<?php

namespace App\Http\Requests\V1;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'string', 'email:rfc', 'max:255'],
            'password'   => ['required', 'string', 'confirmed'],
            'remember'   => ['nullable', 'boolean'],
        ];
    }


    /**
     * Attempt to authenticate the request's credentials.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate($error = false)
    {
        $this->ensureIsNotRateLimited();

        if ($error) {
            RateLimiter::hit($this->throttleKey(), config('auth.decaySeconds'));

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }


    /**
     * @return void
     * @throws ValidationException
     */
    public function isDeleted(): void
    {
        if($user = User::where('email', $this->email)->withTrashed()->first()){
            if(!is_null($user->deleted_at)){
                throw ValidationException::withMessages([
                    'email' => __('Your account has been blocked'),
                ]);
            }
        }
    }


    /**
     * Ensure the login request is not rate limited.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited()
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }


    /**
     * Get the rate limiting throttle key for the request.
     *
     * @return string
     */
    public function throttleKey()
    {
        return Str::lower($this->input('email')) . '|' . $this->ip();
    }


    /**
     * @param  Validator  $validator
     * @return void
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => __('Failed to register user.'),
            'validation_errors' => $validator->errors()
        ], 422));
    }

}
