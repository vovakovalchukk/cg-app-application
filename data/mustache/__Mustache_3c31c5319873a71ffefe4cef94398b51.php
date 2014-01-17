<?php

class __Mustache_3c31c5319873a71ffefe4cef94398b51 extends Mustache_Template
{
    private $lambdaHelper;

    public function renderInternal(Mustache_Context $context, $indent = '')
    {
        $this->lambdaHelper = new Mustache_LambdaHelper($this->mustache, $context);
        $buffer = '';

        $buffer .= $indent . '<label>Status:</label>
';
        $buffer .= $indent . '<div class="custom-select custom-select-sm custom-select-std">
';
        $buffer .= $indent . '    <div class="selected">
';
        $buffer .= $indent . '        <span class="text" data-default="Select">Select</span>
';
        $buffer .= $indent . '        <span class="arrow down">&nbsp;</span>
';
        $buffer .= $indent . '    </div>
';
        $buffer .= $indent . '    <ul>
';
        // 'options.options' section
        $value = $context->findDot('options.options');
        $buffer .= $this->section11a8ed3f52eb98a362448cef90caafee($context, $indent, $value);
        $buffer .= $indent . '    </ul>
';
        $buffer .= $indent . '</div>';

        return $buffer;
    }

    private function section11a8ed3f52eb98a362448cef90caafee(Mustache_Context $context, $indent, $value)
    {
        $buffer = '';
        if (!is_string($value) && is_callable($value)) {
            $source = '
        <li><a href="{{href}}" class="{{class}}">{{title}}</a></li>
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
                $buffer .= $indent . '        <li><a href="';
                $value = $this->resolveValue($context->find('href'), $context, $indent);
                $buffer .= htmlspecialchars($value, 2, 'UTF-8');
                $buffer .= '" class="';
                $value = $this->resolveValue($context->find('class'), $context, $indent);
                $buffer .= htmlspecialchars($value, 2, 'UTF-8');
                $buffer .= '">';
                $value = $this->resolveValue($context->find('title'), $context, $indent);
                $buffer .= htmlspecialchars($value, 2, 'UTF-8');
                $buffer .= '</a></li>
';
                $context->pop();
            }
        }
    
        return $buffer;
    }
}
