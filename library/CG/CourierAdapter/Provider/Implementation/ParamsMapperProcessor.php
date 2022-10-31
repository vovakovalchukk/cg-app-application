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

    protected function getRules(): array
    {
        return ParamsMapperProcessor::RULES;
    }

    private function processMapItem(array $channelRules, array $params): array
    {
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $availableRuleSet = $channelRules[$key] ?? $channelRules;
                $params[$key] = $this->processMapItem($availableRuleSet, $value);
                continue;
            }
            foreach ($channelRules as $chKey => $rule) {
                if ($key != $chKey) {
                    continue;
                }
                $valueToSearchFor = $rule['value'];
                $valueToReplaceWith = $rule['replace'];
                $valuesMatch = strtolower($value) == strtolower($valueToSearchFor);
                $params[$key] = $valuesMatch ? $valueToReplaceWith : $value;
            }
        }
        return $params;
    }

    public function runParamsMapper(string $channelName, array $params): array
    {
        $rules = $this->getRules();
        if (isset($rules[$channelName])) {
            return $this->processMapItem($rules[$channelName], $params);
        }
        return $params;
    }
}
