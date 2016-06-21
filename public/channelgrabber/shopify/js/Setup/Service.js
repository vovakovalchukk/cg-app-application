define(['./EventHandler.js'], function(EventHandler) {
    function Service()
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

        this.getForm = function()
        {
            return form;
        };

        this.setForm = function(newForm)
        {
            form = newForm;
            return this;
        };

        this.getNotification = function()
        {
            return n;
        };

        var init = function()
        {
            this
                .setEventHandler(new EventHandler(this))
                .setForm($(EventHandler.SELECTOR_FORM));
        };

        init.call(this);
    }

    Service.prototype.linkAccount = function()
    {
        var n = this.getNotification();
        n.notice('Linking account');

        this.getForm().ajaxSubmit({
            dataType: "json",
            "success": function(response) {
                if (!response.redirectUrl) {
                    n.error(response.error);
                    return;
                }
                window.location = response.redirectUrl;
            },
            "error": function(response) {
                n.ajaxError(response);
            }
        });
    };

    return Service;
});
