define([
    'Listing/Import/Ajax',
    'DomManipulator'
], function (
    storage,
    domManipulator
) {
    var Service = function ()
    {
        var dataTable;

        this.getDataTable = function()
        {
            return dataTable;
        };

        this.setDataTable = function(newDataTable)
        {
            dataTable = newDataTable;
        };
    };

    Service.SELECTOR_REFRESH_BUTTON_SHADOW = '#refresh-button-shadow';

    Service.prototype.refresh = function()
    {
        if (this.isRefreshing()) {
            return;
        }

        var self = this;
        this.refreshingState();
        storage.refresh(function() {
            self.refreshDatatable();
            self.refreshState();
        });
    };

    Service.prototype.refreshDatatable = function()
    {
        this.getDataTable().redraw();
    };

    Service.prototype.refreshingState = function()
    {
        domManipulator.setHtml(Service.SELECTOR_REFRESH_BUTTON_SHADOW + ' .title', 'Refreshing');
        domManipulator.addClass(Service.SELECTOR_REFRESH_BUTTON_SHADOW, 'disabled');
    };

    Service.prototype.refreshState = function()
    {
        domManipulator.setHtml(Service.SELECTOR_REFRESH_BUTTON_SHADOW + ' .title', 'Refresh');
        domManipulator.removeClass(Service.SELECTOR_REFRESH_BUTTON_SHADOW, 'disabled');
    };

    Service.prototype.isRefreshing = function()
    {
        return domManipulator.hasClass(Service.SELECTOR_REFRESH_BUTTON_SHADOW, 'disabled');
    };

    return new Service();
});