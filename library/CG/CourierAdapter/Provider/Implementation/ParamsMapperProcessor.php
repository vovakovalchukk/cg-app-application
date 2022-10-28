<?php

namespace CG\CourierAdapter\Provider\Implementation;

class ParamsMapperProcessor
{
    /** @var array */
    private $mapperStructure;
    /** @var string */
    private $flag = '';

    public function __construct(array $mapperStructure)
    {
        $this->mapperStructure = $mapperStructure;
    }

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
        if (isset($this->mapperStructure[$channelName])) {
            $rules = $this->mapperStructure[$channelName];
            $this->processMapItem($rules, $params);
            return $params;
        }
        return $params;
    }
}