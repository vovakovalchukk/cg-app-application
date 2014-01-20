<?php

class __Mustache_eed1db1e62b13b1f68f45da5a5c3be45 extends Mustache_Template
{
    private $lambdaHelper;
    protected $strictCallables = true;

    public function renderInternal(Mustache_Context $context, $indent = '')
    {
        $this->lambdaHelper = new Mustache_LambdaHelper($this->mustache, $context);
        $buffer = '';

        $buffer .= $indent . '<div id="filters">
';
        // 'filterRows' section
        $value = $context->find('filterRows');
        $buffer .= $this->section30e0ff35c87260e9f97c2656c3ea043a($context, $indent, $value);
        $buffer .= $indent . '</div>';

        return $buffer;
    }

    private function section89c06a49405d4f5d1bee1c1c8c60a428(Mustache_Context $context, $indent, $value)
    {
        $buffer = '';
        if (is_object($value) && is_callable($value)) {
            $source = '
         {{name}}
        ';
            $result = call_user_func($value, $source, $this->lambdaHelper);
            if (strpos($result, '{{') === false) {
                $buffer .= $result;
            } else {
                $buffer .= $this->mustache
                    ->loadLambda((string) $result)
                    ->renderInternal($context);
            }
        } elseif (!empty($value)) {
            $values = $this->isIterable($value) ? $value : array($value);
            foreach ($values as $value) {
                $context->push($value);
                $buffer .= $indent . '         ';
                $value = $this->resolveValue($context->find('name'), $context, $indent);
                $buffer .= call_user_func($this->mustache->getEscape(), $value);
                $buffer .= '
';
                $context->pop();
            }
        }
    
        return $buffer;
    }

    private function section30e0ff35c87260e9f97c2656c3ea043a(Mustache_Context $context, $indent, $value)
    {
        $buffer = '';
        if (is_object($value) && is_callable($value)) {
            $source = '
    <div class="row">
        {{# filters}}
         {{name}}
        {{/ filters}}
    </div>
    ';
            $result = call_user_func($value, $source, $this->lambdaHelper);
            if (strpos($result, '{{') === false) {
                $buffer .= $result;
            } else {
                $buffer .= $this->mustache
                    ->loadLambda((string) $result)
                    ->renderInternal($context);
            }
        } elseif (!empty($value)) {
            $values = $this->isIterable($value) ? $value : array($value);
            foreach ($values as $value) {
                $context->push($value);
                $buffer .= $indent . '    <div class="row">
';
                // 'filters' section
                $value = $context->find('filters');
                $buffer .= $this->section89c06a49405d4f5d1bee1c1c8c60a428($context, $indent, $value);
                $buffer .= $indent . '    </div>
';
                $context->pop();
            }
        }
    
        return $buffer;
    }
}
