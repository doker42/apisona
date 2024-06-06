<?php

namespace App\Http\Requests\V1;

use App\Helpers\Helper;
use App\Http\Traits\ApiHelperTrait;
use App\Rules\CheckUpdatePassword;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserUpdateRequest extends FormRequest
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
     * @return void
     */
    protected function prepareForValidation()
    {
        $name = $this->request->get('name');
        if ($name) {
            $this->merge(['name' => $name]);
        }

        $email = $this->request->get('email');
        if ($email) {
            $this->merge(['email' => trim($email)]);
        }

        $phone = $this->request->get('phone');
        if ($phone) {
            $this->merge(['phone' => $phone]);
        }

        $language = $this->request->get('language') ?? config('app.locale');
        $this->merge(['language' => $language]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user_passsword = user()->password;
        return [
            'name'       => $this->userNameRule(),
            'phone'      => $this->phoneRule(),
            'email'      => ['string','email:rfc','max:255','unique:users,email,'. auth()->user()->id],
            'language'   => [Rule::in(config('app.locales'))],
            'current_password'  => [
                'nullable',
                'required_with:new_password',
                new CheckUpdatePassword($user_passsword)
            ],
            'new_password' => [
                'required',
                Password::min(8)->letters()->numbers(),
                new CheckUpdatePassword($user_passsword, true)
            ],
        ];
    }


    /**
     * @param Validator $validator
     * @return void
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => __('Failed to update user.'),
            'validation_errors' => $validator->errors()
        ], 422));
    }
}
