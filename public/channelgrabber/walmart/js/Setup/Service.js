import EventHandler from './EventHandler.js';

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

    this.getForm = function()
    {
        return form;
    };

    this.setForm = function(newForm)
    {
        form = newForm;
        return this;
    };

    var init = function()
    {
        this.setEventHandler(new EventHandler(this));
        this.setForm($(EventHandler.SELECTOR_FORM));
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
    this.getForm().ajaxSubmit({
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

export default Service;