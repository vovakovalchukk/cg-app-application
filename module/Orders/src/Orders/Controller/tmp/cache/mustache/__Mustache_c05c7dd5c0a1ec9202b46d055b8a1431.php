<?php

class __Mustache_c05c7dd5c0a1ec9202b46d055b8a1431 extends Mustache_Template
{
    protected $strictCallables = true;
    public function renderInternal(Mustache_Context $context, $indent = '')
    {
        $buffer = '';

        $buffer .= $indent . '<div class="filter clearfix">
';
        $buffer .= $indent . '    <label for="contains-text">';
        $value = $this->resolveValue($context->find('title'), $context, $indent);
        $buffer .= call_user_func($this->mustache->getEscape(), $value);
        $buffer .= '</label>
';
        $buffer .= $indent . '    <input name="contains-text" type="text" placeholder="';
        $value = $this->resolveValue($context->find('placeholder'), $context, $indent);
        $buffer .= call_user_func($this->mustache->getEscape(), $value);
        $buffer .= '" class="';
        $value = $this->resolveValue($context->find('class'), $context, $indent);
        $buffer .= call_user_func($this->mustache->getEscape(), $value);
        $buffer .= '" value="';
        $value = $this->resolveValue($context->find('value'), $context, $indent);
        $buffer .= call_user_func($this->mustache->getEscape(), $value);
        $buffer .= '"/>
';
        $buffer .= $indent . '</div>';

        return $buffer;
    }
}
