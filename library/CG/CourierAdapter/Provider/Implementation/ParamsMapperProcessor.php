<?php

namespace CG\CourierAdapter\Provider\Implementation;

class ParamsMapperProcessor
{
    /** @var array */
    private $rules = [];

    public function setRules(array $rules)
    {
        $this->rules = $rules;
    }

    public function getRules(): array
    {
        return $this->rules;
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
