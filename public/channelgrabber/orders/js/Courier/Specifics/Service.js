define(['./EventHandler.js'], function(EventHandler)
{
    function Service(dataTable)
    {
        var eventHandler;

        this.getDataTable = function()
        {
            return dataTable;
        };

        this.getEventHandler = function()
        {
            return eventHandler;
        };

        this.setEventHandler = function(newEventHandler)
        {
            eventHandler = newEventHandler;
        };

        var init = function()
        {
            this.setEventHandler(new EventHandler(this));
        };
        init.call(this);
    }

    Service.SELECTOR_NAV_FORM = '#courier-specifics-nav-form';

    Service.prototype.courierLinkChosen = function(courierUrl)
    {
        $(Service.SELECTOR_NAV_FORM).attr('action', courierUrl).submit();
    };

    Service.prototype.parcelsChanged = function()
    {
        var parcelData = [];
        $(EventHandler.SELECTOR_PARCEL_INPUT).each(function()
        {
            var input = this;
            parcelData.push({
                name: $(input).attr('name'),
                value: $(input).val()
            });
        });
        this.getDataTable().one("fnServerData", function(event, sSource, aoData, fnCallback, oSettings)
        {
            for (var count in parcelData) {
                aoData.push(parcelData[count]);
            }
        });
        this.getDataTable().cgDataTable('redraw');
    };

    return Service;
});