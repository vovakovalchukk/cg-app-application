define(['../StepAbstract.js'], function(StepAbstract)
{
    function Complete()
    {
        StepAbstract.call(this);
    }

    Complete.prototype = Object.create(StepAbstract.prototype);

    return Complete;
});