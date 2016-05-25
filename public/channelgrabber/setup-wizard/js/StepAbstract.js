define([], function()
{
    function StepAbstract()
    {
        var init = function()
        {
            this.listenForSkip()
                .listenForNext();
        };
        init.call(this);
    }

    StepAbstract.SELECTOR_SKIP = '.setup-wizard-skip-button';
    StepAbstract.SELECTOR_NEXT = '.setup-wizard-next-button';

    StepAbstract.prototype.listenForSkip = function()
    {
        var self = this;
        $(StepAbstract.SELECTOR_SKIP).click(function()
        {
            var button = this;
            var nextUri = $(button).find('.action').data('action');
            self.skip(nextUri);
        });
        return this;
    };

    StepAbstract.prototype.listenForNext = function()
    {
        var self = this;
        $(StepAbstract.SELECTOR_NEXT).click(function()
        {
            var button = this;
            var nextUri = $(button).find('.action').data('action');
            self.next(nextUri);
        });
        return this;
    };

    // Override these in the concrete steps as required
    StepAbstract.prototype.skip = function(nextUri)
    {
        this.next(nextUri);
    };

    StepAbstract.prototype.next = function(nextUri)
    {
        window.location = nextUri;
    };

    return StepAbstract;
});