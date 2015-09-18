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

    Service.prototype.refresh = function()
    {
        var inputData = [];
        $('#datatable td input').each(function()
        {
            var input = this;
            var name = $(input).attr('name');
            if (!name || (!name.match(/^orderData/) && !name.match(/^parcelData/))) {
                return true; // continue
            }
            var value = $(input).val();
            if ($(input).attr('type') == 'checkbox') {
                value = ($(input).is(':checked') ? 1 : 0);
            }
            inputData.push({
                name: name,
                value: value
            });
        });
        // Using one() instead of on() as this data will change each time
        this.getDataTable().one("fnServerData", function(event, sSource, aoData, fnCallback, oSettings)
        {
            for (var count in inputData) {
                aoData.push(inputData[count]);
            }
        });
        this.getDataTable().cgDataTable('redraw');
    };

    return Service;
});