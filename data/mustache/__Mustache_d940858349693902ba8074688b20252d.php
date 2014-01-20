<?php

class __Mustache_d940858349693902ba8074688b20252d extends Mustache_Template
{
    private $lambdaHelper;

    public function renderInternal(Mustache_Context $context, $indent = '')
    {
        $this->lambdaHelper = new Mustache_LambdaHelper($this->mustache, $context);
        $buffer = '';

        $buffer .= $indent . '<div class="sm-element">
';
        $buffer .= $indent . '    <label>';
        $value = $this->resolveValue($context->findDot('options.title'), $context, $indent);
        $buffer .= htmlspecialchars($value, 2, 'UTF-8');
        $buffer .= ':</label>
';
        $buffer .= $indent . '    <div class="custom-select custom-select-sm custom-select-std">
';
        $buffer .= $indent . '        <div class="selected">
';
        $buffer .= $indent . '            <span class="text" data-default="Select">Select</span>
';
        $buffer .= $indent . '            <span class="arrow down">&nbsp;</span>
';
        $buffer .= $indent . '        </div>
';
        $buffer .= $indent . '        <ul>
';
        // 'options.options' section
        $value = $context->findDot('options.options');
        $buffer .= $this->sectionB43dad68f4f0b9998a4e27a3129a9b29($context, $indent, $value);
        $buffer .= $indent . '        </ul>
';
        $buffer .= $indent . '    </div>
';
        $buffer .= $indent . '</div>';

        return $buffer;
    }

    private function sectionB43dad68f4f0b9998a4e27a3129a9b29(Mustache_Context $context, $indent, $value)
    {
        $buffer = '';
        if (!is_string($value) && is_callable($value)) {
            $source = '
            <li><a href="{{href}}" class="{{class}}">{{title}}</a></li>
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
                $buffer .= $indent . '            <li><a href="';
                $value = $this->resolveValue($context->find('href'), $context, $indent);
                $buffer .= htmlspecialchars($value, 2, 'UTF-8');
                $buffer .= '" class="';
                $value = $this->resolveValue($context->find('class'), $context, $indent);
                $buffer .= htmlspecialchars($value, 2, 'UTF-8');
                $buffer .= '">';
                $value = $this->resolveValue($context->find('title'), $context, $indent);
                $buffer .= htmlspecialchars($value, 2, 'UTF-8');
                $buffer .= '</a></li>
';
                $context->pop();
            }
        }
    
        return $buffer;
    }
}
