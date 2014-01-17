<?php

class __Mustache_65f277bcc7190d55fcbe5a7c74842d86 extends Mustache_Template
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
        if ($partial = $this->mustache->loadPartial('customSelectGroupItems')) {
            $buffer .= $partial->renderInternal($context, $indent . '        ');
        }
        $buffer .= $indent . '</div>';

        return $buffer;
    }
}
