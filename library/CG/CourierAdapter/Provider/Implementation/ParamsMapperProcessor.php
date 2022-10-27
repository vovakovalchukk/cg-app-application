<?php

namespace CG\CourierAdapter\Provider\Implementation;

class ParamsMapperProcessor
{
    /** @var array */
    private $mapperStructure;

    public function __construct(array $mapperStructure)
    {
        $this->mapperStructure = $mapperStructure;
    }

    private function collapseData($array, $prefix = '') {
        $result = array();
        foreach($array as $key=>$value) {
            if(is_array($value)) {
                $result = $result + $this->collapseData($value, $prefix . $key . '.');
            }
            else {
                $result[$prefix . $key] = $value;
            }
        }
        return $result;
    }

    private function expandData($data) {
        $output = [];
        foreach ($data as $key => $value) {
            $parts = explode('.', $key);
            $nested = &$output;
            while (count($parts) > 1) {
                $nested = &$nested[array_shift($parts)];
                if (!is_array($nested)) $nested = [];
            }
            $nested[array_shift($parts)] = $value;
        }
        return $output;
    }

    private function processMapItem($channelRules, $params)
    {
        $collapsedRules = $this->collapseData($channelRules);
        $collapsedParams = $this->collapseData($params);

        foreach ($collapsedRules as $paramKey => $rule) {
            if (isset($collapsedParams[$paramKey])) {
                $paramValue = $collapsedParams[$paramKey];
                $callbackRule = [$rule, 'run'];
                $collapsedParams[$paramKey] = is_callable($callbackRule) ? call_user_func($callbackRule, $paramValue) : $rule;
            }
        }

        return $this->expandData($collapsedParams);
    }

    public function runParamsMapper($channelName, $params)
    {
        if (isset($this->mapperStructure[$channelName])) {
            $rules = $this->mapperStructure[$channelName];
            return $this->processMapItem($rules, $params);
        }
        return $params;
    }
}