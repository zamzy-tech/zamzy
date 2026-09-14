<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule;

class TrimmedEnum implements Rule
{
    protected $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public function passes($attribute, $value)
    {
        $value = strtolower(trim($value)); // Trim and lowercase
        $lowerValues = array_map('strtolower', $this->values);
        return in_array($value, $lowerValues);
    }

    public function message()
    {
        return 'The :attribute field is not valid.';
    }
}
