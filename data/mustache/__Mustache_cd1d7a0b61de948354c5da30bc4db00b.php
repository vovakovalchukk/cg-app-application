<?php

class __Mustache_cd1d7a0b61de948354c5da30bc4db00b extends Mustache_Template
{
    public function renderInternal(Mustache_Context $context, $indent = '')
    {
        $buffer = '';

        $buffer .= $indent . '<div class="filter filter-optional clearfix with-close-icon auto-width">
';
        $buffer .= $indent . '    <label>Include Country:</label>
';
        $buffer .= $indent . '        <span class="icon icon-med close" title="remove-filter">Remove Filter</span>
';
        if ($partial = $this->mustache->loadPartial('filters/customSelectGroupItems')) {
            $buffer .= $partial->renderInternal($context, $indent . '        ');
        }
        $buffer .= $indent . '</div>';

        return $buffer;
    }
}
