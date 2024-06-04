<?php

namespace App\Http\Traits;

use Illuminate\Validation\Rules\Password;

trait ApiHelperTrait {

    /**
     * @return string[]
     */
    public function userNameRule($required=false): array
    {
        $rules = ['regex:/^[a-zA-Z\p{Cyrillic}0-9\s`"\'\s]+$/u', 'not_regex:/[0-9]/', 'max:255'];
        return $required ? ['required'] + $rules : $rules;
    }

    /**
     * @return string[]
     */
    public function phoneRule($required=false): array
    {
        $rules = ['regex:/^([0-9\s\-\+\(\)]*)$/', 'min:10', 'max:13'];
        return $required ? array_merge($rules, ['required']) : $rules;
    }

    /**
     * @return string[]
     */
    public function phoneRuleZoho($required=false): array
    {
        $rules = ['regex:/^\\+?[1-9][0-9]*$/', 'min:7', 'max:14'];
        return $required ? array_merge($rules, ['required']) : array_merge($rules, ['nullable']);
    }

    /**
     * @return string[]
     */
    public function occupationRule($required=false): array
    {
        $rules = ['regex:/^[a-zA-Z\p{Cyrillic}0-9\s`"\'\s]+$/u', 'not_regex:/[0-9]/', 'max:255'];
        return $required ? array_merge($rules, ['required']) : $rules;
    }


    public function groupNameRule($required=false): array
    {
        $rules = ['not_regex:/[#$%^&*!()+=\[\]\'\/{}|":<>?~\\\\]/', 'min:2', 'max:255'];
        return $required ? array_merge($rules, ['required']) : $rules;
    }
    /**
     * @return string
     */
    public function phoneRegex(): string
    {
        return '/^([0-9\s\-\+\(\)]*)$/';
    }

    /**
     * @return string
     */
    public function domainRegex(): string
    {
        return '/^(?!-)[A-Za-z0-9-]{1,63}(?<!-)$/';
    }

    /**
     * @return string
     */
    public function customDomainRegex(): string
    {
        return '/^((?!-)[A-Za-z0-9-]{1,63}(?<!-)\\.)+[A-Za-z]{2,6}$/';
    }


    /**
     * @return string
     */
    public function colorRegex(): string
    {
        return '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/';
    }


    /**
     * @return string
     */
    public function slugRegex(): string
    {
        return '/^[a-zA-Z0-9][a-zA-Z0-9-\_\-]+[a-zA-Z0-9]$/';
    }


    /**
     * @param $model
     * @param string $email
     * @param bool $trashed
     * @return object|null
     */
    public function getObjectByEmail($model, string $email, bool $trashed=false): object|null
    {
        return $trashed
            ? $model::onlyTrashed()->where('email', $email)->first()
            : $model::where('email', $email)->first();
    }


    /**
     * for delimiters ["_", "-"]
     *
     * @param $str
     * @return array|string
     */
    public function parseString($str): array|string
    {
        $arr = explode("_", $str);
        $result = [];
        foreach($arr as $el){
            if (str_contains($el, '-')) {
                $result = array_merge($result,explode("-", $el));
            } else {
                $result[] = $el;
            }
        }

        return $result;
    }

}
