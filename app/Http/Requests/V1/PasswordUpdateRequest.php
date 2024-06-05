<?php

namespace App\Http\Requests\V1;

use App\Http\Traits\ApiHelperTrait;
use App\Rules\CheckUpdatePassword;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\Password;

class PasswordUpdateRequest extends FormRequest
{

    use ApiHelperTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
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
        $user_passsword = auth()->user()->password;

        return [
            'current_password' => [
                'required',
                new CheckUpdatePassword($user_passsword)
            ],
            'new_password' => [
                'required',
                Password::min(8)->letters()->numbers(),
                new CheckUpdatePassword($user_passsword, true)
            ]
        ];
    }

    /**
     * @param Validator $validator
     * @return void
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => __('Failed to change your password. Please, try again.'),
            'validation_errors' => $validator->errors()
        ], 422));
    }
}
