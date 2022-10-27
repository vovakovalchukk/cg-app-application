<?php

namespace CG\CourierAdapter\Provider\Implementation\ParamsMapperRules;

class PostcodeValidationRule
{
    public static function run($val)
    {
        if (strtolower($val) == 'no') {
            return 0;
        }
        return $val;
    }
}