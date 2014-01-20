<?php

class __Mustache_5395be497ef1cadfe49e15140e061ae1 extends Mustache_Template
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
        if ($partial = $this->mustache->loadPartial('customSelect')) {
            $buffer .= $partial->renderInternal($context, $indent . '');
        }
        $buffer .= 's
';
        $buffer .= $indent . '</div>
';

        return $buffer;
    }
}
