<?php

class __Mustache_3c274c46c6e0f77509f1fca5e915715e extends Mustache_Template
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
        $buffer .= $this->section8ad2d2243807a837b5b07c74220a9949($context, $indent, $value);
        $buffer .= $indent . '</div>';

        return $buffer;
    }

    private function section4ccde19b9c3dddb7c141144ee879d615(Mustache_Context $context, $indent, $value)
    {
        $buffer = '';
        if (is_object($value) && is_callable($value)) {
            $source = '
          {{{.}}}
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
                $buffer .= $indent . '          ';
                $value = $this->resolveValue($context->last(), $context, $indent);
                $buffer .= $value;
                $buffer .= '
';
                $context->pop();
            }
        }
    
        return $buffer;
    }

    private function section05f5653432ae7807597ef1b102322d02(Mustache_Context $context, $indent, $value)
    {
        $buffer = '';
        if (is_object($value) && is_callable($value)) {
            $source = '
          {{#.}}
          {{{.}}}
          {{/.}}
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
                // '.' section
                $value = $context->last('.');
                $buffer .= $this->section4ccde19b9c3dddb7c141144ee879d615($context, $indent, $value);
                $context->pop();
            }
        }
    
        return $buffer;
    }

    private function section8ad2d2243807a837b5b07c74220a9949(Mustache_Context $context, $indent, $value)
    {
        $buffer = '';
        if (is_object($value) && is_callable($value)) {
            $source = '
    <div class="row">
        {{# filters}}
          {{#.}}
          {{{.}}}
          {{/.}}
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
                $buffer .= $this->section05f5653432ae7807597ef1b102322d02($context, $indent, $value);
                $buffer .= $indent . '    </div>
';
                $context->pop();
            }
        }
    
        return $buffer;
    }
}
