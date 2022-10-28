<?php

namespace CG\CourierAdapter\Provider\Implementation;

class ParamsMapper
{
    const RULES = [
        // Courier name (DPD) we want to apply the rule
        "dpd-ca" => [
            // Parameters structure we target
            'AccountInformation.postcodeValidation' => ['value' => 'no', 'replace' => '0'],
        ],
        // Courier name (DPD Local) we want to apply the rule
        "interlink-ca" => [
            // Parameters structure we target
            'AccountInformation.postcodeValidation' => ['value' => 'no', 'replace' => '0']
        ]
    ];
}

