<?php

namespace CG\CourierAdapter\Provider\Implementation;

use CG\CourierAdapter\Provider\Implementation\ParamsMapperRules\PostcodeValidationRule;

class ParamsMapper
{
    const RULES = [
        // Courier name (DPD) we want to apply the rule
        "dpd-ca" => [
            // Parameters structure we target
            'AccountInformation' => [
                "postcodeValidation" => PostcodeValidationRule::class
            ]
        ],
        // Courier name (DPD Local) we want to apply the rule
        "interlink-ca" => [
            // Parameters structure we target
            'AccountInformation' => [
                "postcodeValidation" => PostcodeValidationRule::class
            ]
        ]
    ];
}

