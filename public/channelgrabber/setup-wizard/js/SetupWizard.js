define(['popup/confirm'], function(Confirm)
{
    function SetupWizard()
    {
        var stepName;
        var skipCallbacks = [];
        var nextCallbacks = [];

        this.setStepName = function(newStepName)
        {
            stepName = newStepName;
            return this;
        };

        this.getStepName = function()
        {
            return stepName;
        };

        this.getSkipCallbacks = function()
        {
            return skipCallbacks;
        };

        this.getNextCallbacks = function()
        {
            return nextCallbacks;
        };
    }

    SetupWizard.SELECTOR_STEPS = '.setup-wizard-sidebar ul li ul li';
    SetupWizard.SELECTOR_SKIP = '.setup-wizard-skip-button';
    SetupWizard.SELECTOR_NEXT = '.setup-wizard-next-button';

    SetupWizard.prototype.registerSkipCallback = function(callback)
    {
        if (typeof callback != 'function') {
            throw 'Invalid callback passed to registerSkipCallback()';
        }
        this.getSkipCallbacks().push(callback);
    };

    SetupWizard.prototype.registerNextCallback = function(callback)
    {
        if (typeof callback != 'function') {
            throw 'Invalid callback passed to registerNextCallback()';
        }
        this.getNextCallbacks().push(callback);
    };

    SetupWizard.prototype.registerSkipConfirmation = function(message)
    {
        this.registerSkipCallback(function()
        {
            return new Promise(function(resolve, reject)
            {
                var confirm = new Confirm(message, function(answer)
                {
                    if (answer == Confirm.VALUE_YES) {
                        resolve();
                    } else if (answer == Confirm.VALUE_NO) {
                        reject();
                    }
                })
            });
        });
    };

    SetupWizard.prototype.init = function(stepName)
    {
        this.setStepName(stepName)
            .numberSteps()
            .listenForSkip()
            .listenForNext();
        return this;
    };

    SetupWizard.prototype.numberSteps = function()
    {
        var steps = $(SetupWizard.SELECTOR_STEPS);
        steps.each(function(index)
        {
            var step = this;
            var stepNo = index + 1;
            var label = $(step).find('.label');
            // Special case for the last step: don't number it
            if (stepNo == steps.length) {
                label.html('<span class="setup-wizard-step-complete">' + label.text() + '</span>');
                return true; // continue
            }
            label.prepend('<span class="setup-wizard-step-number">Step ' + stepNo + '</span>');
        });
        return this;
    };

    SetupWizard.prototype.listenForSkip = function()
    {
        var self = this;
        $(SetupWizard.SELECTOR_SKIP).on('click.setupWizard', function()
        {
            var button = this;
            var nextUri = $(button).find('.action').data('action');
            self.skip(nextUri);
        });
        return this;
    };

    SetupWizard.prototype.listenForNext = function()
    {
        var self = this;
        $(SetupWizard.SELECTOR_NEXT).on('click.setupWizard', function()
        {
            var button = this;
            var nextUri = $(button).find('.action').data('action');
            self.next(nextUri);
        });
        return this;
    };

    SetupWizard.prototype.skip = function(nextUri)
    {
        var callbacks = this.getSkipCallbacks();
        this.loadNextIfCallbacksPass(nextUri, 'skipped', callbacks);
    };

    SetupWizard.prototype.next = function(nextUri)
    {
        var callbacks = this.getNextCallbacks();
        this.loadNextIfCallbacksPass(nextUri, 'completed', callbacks);
    };

    SetupWizard.prototype.loadNextIfCallbacksPass = function(nextUri, status, callbacks)
    {
        var promises = [];
        for (var key in callbacks) {
            var result = callbacks[key]();
            if (result === false) {
                return;
            } else if (result instanceof Promise) {
                promises.push(result);
            }
        }
        if (promises.length == 0) {
            return this.loadNext(nextUri, status);
        }
        var self = this;
        Promise.all(promises).then(function()
        {
            // all succeeded, continue
            self.loadNext(nextUri, status);
        }, function(err)
        {
            // No-op
        });
    };

    SetupWizard.prototype.loadNext = function(nextUri, status)
    {
        var form = '<form method="POST" action="' + nextUri + '">';
        form += '<input type="hidden" name="step" value="' + this.getStepName() + '" />';
        form += '<input type="hidden" name="status" value="' + status + '" />';
        $(form).appendTo('body').submit().remove();
    };

    return new SetupWizard();
});