define([], function()
{
    function SetupWizard()
    {
        var init = function()
        {
            this.numberSteps();
        };
        init.call(this);
    }

    SetupWizard.SELECTOR_STEPS = '.setup-wizard-sidebar ul li ul li';

    SetupWizard.prototype.numberSteps = function()
    {
        var steps = $(SetupWizard.SELECTOR_STEPS);
        steps.each(function(index)
        {
            var step = this;
            var stepNo = index + 1;
            $(step).find('.label').prepend('<span class="setup-wizard-step-number">Step ' + stepNo + '</span>');
        });
    };

    return SetupWizard;
});