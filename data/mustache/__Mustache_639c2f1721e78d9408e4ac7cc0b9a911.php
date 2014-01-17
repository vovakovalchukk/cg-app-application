<?php

class __Mustache_639c2f1721e78d9408e4ac7cc0b9a911 extends Mustache_Template
{
    public function renderInternal(Mustache_Context $context, $indent = '')
    {
        $buffer = '';

        $buffer .= $indent . '<div class="med-element">
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
        $buffer .= $indent . '</div>';

        return $buffer;
    }
}
