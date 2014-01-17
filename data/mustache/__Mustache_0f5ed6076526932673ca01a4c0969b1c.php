<?php

class __Mustache_0f5ed6076526932673ca01a4c0969b1c extends Mustache_Template
{
    private $lambdaHelper;

    public function renderInternal(Mustache_Context $context, $indent = '')
    {
        $this->lambdaHelper = new Mustache_LambdaHelper($this->mustache, $context);
        $buffer = '';

        $buffer .= $indent . '<div class="custom-select">
';
        $buffer .= $indent . '<label>Columns:</label>
';
        $buffer .= $indent . '<div class="custom-select custom-select-group custom-select-concatenate large hover-no-scroll">
';
        $buffer .= $indent . '    <div class="selected">
';
        $buffer .= $indent . '        <span class="selected-content">Select</span>
';
        $buffer .= $indent . '        <span class="arrow down">&nbsp;</span>
';
        $buffer .= $indent . '    </div>
';
        $buffer .= $indent . '
';
        $buffer .= $indent . '    <div class="open-content">
';
        $buffer .= $indent . '        <div class="search-selected-wrapper clearfix">
';
        $buffer .= $indent . '            <input class="search-selected" type="text" placeholder="Search..." value=""/>
';
        $buffer .= $indent . '            <span class="icon-med inverted search">Search</span>
';
        $buffer .= $indent . '        </div>
';
        $buffer .= $indent . '        <div class="custom-select-actions">
';
        $buffer .= $indent . '            <a class="select-all">Select All</a> <a class="clear-action">Clear Selected</a>
';
        $buffer .= $indent . '        </div>
';
        $buffer .= $indent . '        <ul>
';
        // 'options' section
        $value = $context->find('options');
        $buffer .= $this->section5ce64bd91ebf280c7b18027b81392a2c($context, $indent, $value);
        $buffer .= $indent . '            <li><a class="no-results hidden">No results</a></li>
';
        $buffer .= $indent . '        </ul>
';
        $buffer .= $indent . '    </div>
';
        $buffer .= $indent . '</div>
';
        $buffer .= $indent . '</div>';

        return $buffer;
    }

    private function section5ce64bd91ebf280c7b18027b81392a2c(Mustache_Context $context, $indent, $value)
    {
        $buffer = '';
        if (!is_string($value) && is_callable($value)) {
            $source = '
            <li><a href="#" class="custom-select-item" title="{{.}}"><input type="checkbox"  />{{.}}</a></li>
            ';
            $result = call_user_func($value, $source, $this->lambdaHelper);
            if (strpos($result, '{{') === false) {
                $buffer .= $result;
            } else {
                $buffer .= $this->mustache
                    ->loadLambda((string) $result)
                    ->renderInternal($context);
            }
        } elseif (!empty($value)) {
            $values = $this->isIterable($value) ? $value : array($value);
            foreach ($values as $value) {
                $context->push($value);
                $buffer .= $indent . '            <li><a href="#" class="custom-select-item" title="';
                $value = $this->resolveValue($context->last(), $context, $indent);
                $buffer .= htmlspecialchars($value, 2, 'UTF-8');
                $buffer .= '"><input type="checkbox"  />';
                $value = $this->resolveValue($context->last(), $context, $indent);
                $buffer .= htmlspecialchars($value, 2, 'UTF-8');
                $buffer .= '</a></li>
';
                $context->pop();
            }
        }
    
        return $buffer;
    }
}
