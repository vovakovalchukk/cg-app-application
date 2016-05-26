define(['../SetupWizard.js'], function(setupWizard)
{
    function Example()
    {
        this.getSetupWizard = function()
        {
            return setupWizard;
        };

        var init = function()
        {
            this.addSkipConfirmCallback();
        };
        init.call(this);
    }

    Example.prototype.addSkipConfirmCallback = function()
    {
        this.getSetupWizard().registerSkipConfirmation('Are you really, really, realy sure?');
    };

    return Example;
});