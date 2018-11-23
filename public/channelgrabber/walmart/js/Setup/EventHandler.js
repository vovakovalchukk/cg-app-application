var EventHandler = function(service)
{
    this.getService = function()
    {
        return service;
    };

    var init = function()
    {
        this.initSubmitListener()
            .initEnterListener();
    };
    init.call(this);
};

EventHandler.SELECTOR_FORM = '#carrier-account-form';
EventHandler.SELECTOR_LINK_ACCOUNT = '#linkAccount';
EventHandler.KEY_ENTER = 13;

EventHandler.prototype.initSubmitListener = function()
{
    var service = this.getService();
    $(EventHandler.SELECTOR_LINK_ACCOUNT).off('click').on('click', function()
    {
        service.save();
    });
    $(EventHandler.SELECTOR_FORM).off('submit').on('submit', function(event)
    {
        return false;
    });
    return this;
};

EventHandler.prototype.initEnterListener = function()
{
    var service = this.getService();
    $(EventHandler.SELECTOR_FORM + ' input').off('keypress').on('keypress', function(e)
    {
        if (e.which != EventHandler.KEY_ENTER) {
            return;
        }
    });
    return this;
};

export default EventHandler;