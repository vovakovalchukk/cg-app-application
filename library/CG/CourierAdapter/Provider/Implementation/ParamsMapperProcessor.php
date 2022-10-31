<?php

namespace CG\CourierAdapter\Provider\Implementation;

class ParamsMapperProcessor
{
    const RULES = [
        // Courier name (DPD) we want to apply the rule
        "dpd-ca" => [
            // Parameters structure we target
            'AccountInformation' => [
                'postcodeValidation' => ['value' => 'no', 'replace' => '0'],
            ],
        ],

        // Courier name (DPD Local) we want to apply the rule
        "interlink-ca" => [
            // Parameters structure we target
            'AccountInformation' => [
                'postcodeValidation' => ['value' => 'no', 'replace' => '0'],
            ],
        ]
    ];

    private function processMapItem(string $channelRules, array $params)
    {
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $availableRuleSet = $channelRules[$key] ?? $channelRules;
                $params[$key] = $this->processMapItem($availableRuleSet, $value);
            } else {
                foreach ($channelRules as $chKey => $rule) {
                    if ($key == $chKey) {
                        $valueToSearchFor = $rule['value'];
                        $valueToReplaceWith = $rule['replace'];
                        $valuesMatch = strtolower($value) == strtolower($valueToSearchFor);
                        $params[$key] = $valuesMatch ? $valueToReplaceWith : $value;
                    }
                }

            }
        }
        return $params;
    }

    public function runParamsMapper($channelName, $params)
    {
        if (isset(ParamsMapperProcessor::RULES[$channelName])) {
            return $this->processMapItem(ParamsMapperProcessor::RULES[$channelName], $params);
        }
        return $params;
    }
}
