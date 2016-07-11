define([
    './EventHandler.js'
], function (
    EventHandler
) {
    var Service = function()
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

        var init = function()
        {
            this.setEventHandler(new EventHandler(this));
        };
        init.call(this);
    };

    Service.prototype.save = function()
    {
        var valid = this.validate();
        if (!valid) {
            return;
        }
        n.notice('Connecting Account');
        $(EventHandler.SELECTOR_FORM).ajaxSubmit({
            "dataType": "json",
            "success": function(response) {
                n.success('Account connected');
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