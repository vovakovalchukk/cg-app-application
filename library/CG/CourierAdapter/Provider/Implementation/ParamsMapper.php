<?php

namespace CG\CourierAdapter\Provider\Implementation;

use CG\CourierAdapter\Provider\Implementation\ParamsMapperRules\PostcodeValidationRule;

class ParamsMapper
{
    const RULES = [
        // Courier name we want to apply the rule
        "dpd-ca" => [
            // Parameters structure we target
            'AccountInformation' => [
                "postcodeValidation" => PostcodeValidationRule::class
            ]
        ]
    ];
}

