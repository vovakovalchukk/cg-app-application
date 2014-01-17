<?php

class __Mustache_71636e255a83d1a3033f1816f4fd4c22 extends Mustache_Template
{
    public function renderInternal(Mustache_Context $context, $indent = '')
    {
        $buffer = '';

        $buffer .= $indent . '<div id="filters">
';
        $buffer .= $indent . '    <div class="row">
';
        $buffer .= $indent . '        ';
        $value = $this->resolveValue($context->find('filterButtons'), $context, $indent);
        $buffer .= htmlspecialchars($value, 2, 'UTF-8');
        $buffer .= '
';
        $buffer .= $indent . '    </div>
';
        $buffer .= $indent . '</div>
';

        return $buffer;
    }
}
