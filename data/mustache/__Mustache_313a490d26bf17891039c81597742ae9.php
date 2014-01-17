<?php

class __Mustache_313a490d26bf17891039c81597742ae9 extends Mustache_Template
{
    public function renderInternal(Mustache_Context $context, $indent = '')
    {
        $buffer = '';

        $buffer .= $indent . '<div class="filter filter-sm clearfix ';
        $value = $this->resolveValue($context->findDot('options.id'), $context, $indent);
        $buffer .= htmlspecialchars($value, 2, 'UTF-8');
        $buffer .= '">
';
        $buffer .= $indent . '    <label>';
        $value = $this->resolveValue($context->findDot('options.title'), $context, $indent);
        $buffer .= htmlspecialchars($value, 2, 'UTF-8');
        $buffer .= '</label>
';
        $buffer .= $indent . '    ';
        $value = $this->resolveValue($context->findDot('options.customSelect'), $context, $indent);
        $buffer .= htmlspecialchars($value, 2, 'UTF-8');
        $buffer .= '
';
        $buffer .= $indent . '</div>
';

        return $buffer;
    }
}
