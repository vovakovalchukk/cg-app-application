define(['../SetupWizard.js'], function(setupWizard)
{
    function Channels()
    {
        this.getSetupWizard = function()
        {
            return setupWizard;
        };

        var init = function()
        {
            
        };
        init.call(this);
    }

    return Channels;
});