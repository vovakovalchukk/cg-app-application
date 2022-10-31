<?php

namespace CG\CourierAdapter\Provider\Implementation;

class ParamsMapperProcessor
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

    /** @var string */
    private $flag = '';

    private function processMapItem($channelRules, &$params)
    {
        foreach ($params as $key => &$value) {
            if (is_array($value)) {
                $this->flag = $key . '.';
                $this->processMapItem($channelRules, $value);
            } else {
                $keyToCheck = $this->flag . $key;
                if (isset($channelRules[$keyToCheck])) {
                    $rule = $channelRules[$keyToCheck];
                    $valueToSearchFor = $rule['value'];
                    $valueToReplaceWith = $rule['replace'];
                    $valuesMatch = strtolower($value) == strtolower($valueToSearchFor);
                    $params[$key] = $valuesMatch ? $valueToReplaceWith : $value;
                }
            }
        }
    }

    public function runParamsMapper($channelName, $params)
    {
        if (isset(ParamsMapperProcessor::RULES[$channelName])) {
            $this->processMapItem(ParamsMapperProcessor::RULES[$channelName], $params);
        }
        return $params;
    }
}
