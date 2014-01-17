<?php

class __Mustache_ca28696ce0d27a54026ccb6083e340e4 extends Mustache_Template
{
    private $lambdaHelper;

    public function renderInternal(Mustache_Context $context, $indent = '')
    {
        $this->lambdaHelper = new Mustache_LambdaHelper($this->mustache, $context);
        $buffer = '';

        $buffer .= $indent . '<div class="filter auto-width">
';
        // 'buttons' section
        $value = $context->find('buttons');
        $buffer .= $this->section0ba4111c981e60bdbd14c66afef26821($context, $indent, $value);
        $buffer .= $indent . '</div>';

        return $buffer;
    }

    private function section0ba4111c981e60bdbd14c66afef26821(Mustache_Context $context, $indent, $value)
    {
        $buffer = '';
        if (!is_string($value) && is_callable($value)) {
            $source = '
    <input type="button" class="button {{class}}" value="{{value}}" name="{{name}}" data-action="{{action}}" />
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
                $buffer .= $indent . '    <input type="button" class="button ';
                $value = $this->resolveValue($context->find('class'), $context, $indent);
                $buffer .= htmlspecialchars($value, 2, 'UTF-8');
                $buffer .= '" value="';
                $value = $this->resolveValue($context->find('value'), $context, $indent);
                $buffer .= htmlspecialchars($value, 2, 'UTF-8');
                $buffer .= '" name="';
                $value = $this->resolveValue($context->find('name'), $context, $indent);
                $buffer .= htmlspecialchars($value, 2, 'UTF-8');
                $buffer .= '" data-action="';
                $value = $this->resolveValue($context->find('action'), $context, $indent);
                $buffer .= htmlspecialchars($value, 2, 'UTF-8');
                $buffer .= '" />
';
                $context->pop();
            }
        }
    
        return $buffer;
    }
}
