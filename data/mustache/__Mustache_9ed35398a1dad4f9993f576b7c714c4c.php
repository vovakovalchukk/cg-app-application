<?php

class __Mustache_9ed35398a1dad4f9993f576b7c714c4c extends Mustache_Template
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
        $value = $this->resolveValue($context->findDot('options.custom-select'), $context, $indent);
        $buffer .= htmlspecialchars($value, 2, 'UTF-8');
        $buffer .= '
';
        $buffer .= $indent . '</div>
';

        return $buffer;
    }
}
