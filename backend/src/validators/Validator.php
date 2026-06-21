<?php

namespace App\Validators;

class Validator
{

    /**
     * Validate an input array of data against specified validation rules.
     * 
     * @param array $data Input data key-value array (e.g. $_POST or decoded JSON request)
     * @param array $rules Rules array (e.g., ['email' => 'required|email', 'password' => 'required|min:6'])
     * @return array Array of validation errors, or empty array if all pass
     */
    public static function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $ruleString) {
            $value = isset($data[$field]) ? trim($data[$field]) : '';
            $individualRules = explode('|', $ruleString);

            foreach ($individualRules as $rule) {
                // Rule: required
                if ($rule === 'required') {
                    if ($value === '') {
                        $errors[$field][] = "Trường này là bắt buộc.";
                        break; // Skip subsequent rules if required fails
                    }
                }

                // Skip other validations if value is empty and not required
                if ($value === '') {
                    continue;
                }

                // Rule: email
                if ($rule === 'email') {
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[$field][] = "Định dạng email không hợp lệ.";
                    }
                }

                // Rule: phone (exactly 10 digits starting with 0)
                if ($rule === 'phone') {
                    if (!preg_match('/^0[0-9]{9}$/', $value)) {
                        $errors[$field][] = "Số điện thoại phải gồm 10 chữ số và bắt đầu bằng số 0.";
                    }
                }

                // Rule: min:N
                if (strncmp($rule, 'min:', 4) === 0) {
                    $minVal = (int)substr($rule, 4);
                    if (mb_strlen($value) < $minVal) {
                        $errors[$field][] = "Trường này phải có tối thiểu {$minVal} ký tự.";
                    }
                }

                // Rule: max:N
                if (strncmp($rule, 'max:', 4) === 0) {
                    $maxVal = (int)substr($rule, 4);
                    if (mb_strlen($value) > $maxVal) {
                        $errors[$field][] = "Trường này chỉ được tối đa {$maxVal} ký tự.";
                    }
                }
            }
        }

        return $errors;
    }
}
