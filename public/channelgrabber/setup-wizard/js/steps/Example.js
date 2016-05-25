define(['../StepAbstract.js'], function(StepAbstract)
{
    function Example()
    {
        StepAbstract.call(this);
    }

    Example.prototype = Object.create(StepAbstract.prototype);

    return Example;
});