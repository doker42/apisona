<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Hash;

class CheckUpdatePassword implements ValidationRule
{
    public string $user_password;

    public bool $invert;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(string $user_password, bool $invert = false)
    {
        $this->user_password = $user_password;
        $this->invert = $invert;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        /** check new password */
        if ($this->invert) {
            if (Hash::check($value, $this->user_password)) {
                $fail(__('The :attribute is equal with new password'));
            }
        }
        /** check current password */
        else {
            if (!is_null($value)){
                if (!Hash::check($value, $this->user_password)) {
                    $fail(__('The :attribute is incorrect.'));
                }
            }
        }
    }
}
