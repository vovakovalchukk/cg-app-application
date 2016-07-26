define([
    './EventHandler.js'
], function (
    EventHandler
) {
    var Service = function(savingNotification, savedNotification)
    {
        var eventHandler;
        var form;

        this.getEventHandler = function()
        {
            return eventHandler;
        };

        this.setEventHandler = function(newEventHandler)
        {
            eventHandler = newEventHandler;
            return this;
        };

        this.getSavingNotification = function()
        {
            return savingNotification;
        };

        this.getSavedNotification = function()
        {
            return savedNotification;
        };

        var init = function()
        {
            this.setEventHandler(new EventHandler(this));
        };
        init.call(this);
    };

    Service.prototype.save = function()
    {
        var self = this;
        var valid = this.validate();
        if (!valid) {
            return;
        }
        n.notice(this.getSavingNotification());
        $(EventHandler.SELECTOR_FORM).ajaxSubmit({
            "dataType": "json",
            "success": function(response) {
                n.success(self.getSavedNotification());
                window.location = response.redirectUrl;
            },
            "error": function(response) {
                n.ajaxError(response);
            }
        });
    };

    Service.prototype.validate = function()
    {
        var errors = [];
        $(EventHandler.SELECTOR_FORM+' input.required').each(function()
        {
            if ($(this).val() === '') {
                errors.push($(this).attr('name'));
            }
        });
        if (errors.length > 0) {
            n.error('The following fields are required: '+errors.join(', '));
            return false;
        }
        return true;
    };

    return Service;
});