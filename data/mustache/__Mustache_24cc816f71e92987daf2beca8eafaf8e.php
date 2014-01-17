<?php

class __Mustache_24cc816f71e92987daf2beca8eafaf8e extends Mustache_Template
{
    public function renderInternal(Mustache_Context $context, $indent = '')
    {
        $buffer = '';

        $buffer .= $indent . '<div class="filter clearfix">
';
        $buffer .= $indent . '    <label for="contains-text">';
        $value = $this->resolveValue($context->findDot('options.title'), $context, $indent);
        $buffer .= htmlspecialchars($value, 2, 'UTF-8');
        $buffer .= '</label>
';
        $buffer .= $indent . '    <input name="contains-text" type="text" placeholder="';
        $value = $this->resolveValue($context->findDot('options.placeholder'), $context, $indent);
        $buffer .= htmlspecialchars($value, 2, 'UTF-8');
        $buffer .= '" class="';
        $value = $this->resolveValue($context->findDot('options.class'), $context, $indent);
        $buffer .= htmlspecialchars($value, 2, 'UTF-8');
        $buffer .= '" value="';
        $value = $this->resolveValue($context->findDot('options.value'), $context, $indent);
        $buffer .= htmlspecialchars($value, 2, 'UTF-8');
        $buffer .= '"/>
';
        $buffer .= $indent . '</div>
';

        return $buffer;
    }
}
