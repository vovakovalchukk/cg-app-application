<?php

class __Mustache_10073de399b23d64d3998148459bce9a extends Mustache_Template
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
        if ($partial = $this->mustache->loadPartial('customSelect')) {
            $buffer .= $partial->renderInternal($context, $indent . '    ');
        }
        $buffer .= $indent . '</div>
';

        return $buffer;
    }
}
