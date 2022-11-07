<?php

namespace CG\CourierAdapter\Provider\Implementation;

use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;

class ParamsMapperProcessor implements LoggerAwareInterface
{
    use LogTrait;

    private const LOG_CODE  = 'ParamsMapperProcessorService';
    private const LOG_STEP_BEFORE  = 'before';
    private const LOG_STEP_AFTER  = 'after';
    private const LOG_MSG = 'running extra data mapper on request params';

    private const RULES = [
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
        $this->logDebug(self::LOG_MSG,  $params, [self::LOG_CODE, self::LOG_STEP_BEFORE]);
        if (isset(self::RULES[$channelName])) {
            $params = $this->processMapItem(self::RULES[$channelName], $params);
            $this->logDebug(self::LOG_MSG, $params, [self::LOG_CODE, self::LOG_STEP_AFTER]);
        }
        return $params;
    }
}
