<?php

class __Mustache_69f80d5179bb5ac75da7fa3d0751c879 extends Mustache_Template
{
    private $lambdaHelper;

    public function renderInternal(Mustache_Context $context, $indent = '')
    {
        $this->lambdaHelper = new Mustache_LambdaHelper($this->mustache, $context);
        $buffer = '';

        $buffer .= $indent . '<div class="date-range clearfix">
';
        $buffer .= $indent . '    <label for="date-range">Date Range</label>
';
        $buffer .= $indent . '    <input name="date-range" class="date-range-input" value=""/>
';
        $buffer .= $indent . '    <span class="toggle icon-med inverted calendar">&nbsp;</span>
';
        $buffer .= $indent . '    <input type="hidden" name="date-range-from" class="date-range-input-from" value="" />
';
        $buffer .= $indent . '    <input type="hidden" name="date-range-to" class="date-range-input-to" value="" />
';
        $buffer .= $indent . '    <div class="date-range-menu">
';
        $buffer .= $indent . '        <ul>
';
        // 'options' section
        $value = $context->find('options');
        $buffer .= $this->sectionAeaede3365f00dc2eeadd9620737dcf5($context, $indent, $value);
        $buffer .= $indent . '            <li class="date-range-option with-calendar">
';
        $buffer .= $indent . '                <a href="#" class="date-range-option">Date Range <span class="arrow right"></span></a>
';
        $buffer .= $indent . '                <div class="date-range-calendar">
';
        $buffer .= $indent . '                    <div class="datepicker from"></div>
';
        $buffer .= $indent . '                    <div class="datepicker to"></div>
';
        $buffer .= $indent . '                    <div class="buttons">
';
        $buffer .= $indent . '                        <span class="button close">Apply</span>
';
        $buffer .= $indent . '                    </div>
';
        $buffer .= $indent . '                </div>
';
        $buffer .= $indent . '            </li>
';
        $buffer .= $indent . '        </ul>
';
        $buffer .= $indent . '    </div>
';
        $buffer .= $indent . '</div>';

        return $buffer;
    }

    private function sectionAeaede3365f00dc2eeadd9620737dcf5(Mustache_Context $context, $indent, $value)
    {
        $buffer = '';
        if (!is_string($value) && is_callable($value)) {
            $source = '
            <li class="date-range-option"><a href="#" data-range-from="{{from}}" data-range-to="{{to}}">{{title}}</a></li>
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
                $buffer .= $indent . '            <li class="date-range-option"><a href="#" data-range-from="';
                $value = $this->resolveValue($context->find('from'), $context, $indent);
                $buffer .= htmlspecialchars($value, 2, 'UTF-8');
                $buffer .= '" data-range-to="';
                $value = $this->resolveValue($context->find('to'), $context, $indent);
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
