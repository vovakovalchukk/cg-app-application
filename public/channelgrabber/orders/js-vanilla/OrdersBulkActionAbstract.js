define([
    'BulkActionAbstract',
    'Orders/BulkActionService'
], function(
    BulkActionAbstract,
    bulkActionService
) {
    function OrdersBulkActionAbstract()
    {
        BulkActionAbstract.call(this);

        this.getElement = function()
        {
            return $(this.getButtonSelector());
        };

        this.getDataTableElement = function()
        {
            return $('#' + this.getElement().data('datatable'));
        };
    }

    OrdersBulkActionAbstract.SELECTOR_FILTER_BAR = '#filters';
    OrdersBulkActionAbstract.URL_SAVE_FILTER = '/orders/bulkActionFilter';

    OrdersBulkActionAbstract.prototype = Object.create(BulkActionAbstract.prototype);

    OrdersBulkActionAbstract.prototype.getDataToSubmit = function()
    {
        var filterBar = $(OrdersBulkActionAbstract.SELECTOR_FILTER_BAR);
        if (filterBar.data('id')) {
            return {"filterId": filterBar.data('id')};
        }
        if (this.isAllSelected() && !this.isAllRecordsLoaded()) {
            return this.getFilterData();
        }
        return this.getOrderData();
    };

    OrdersBulkActionAbstract.prototype.isAllSelected = function()
    {
        var selectAllDomId = this.getElement().data('datatable') + '-select-all';
        return $('#'+selectAllDomId).is(':checked');
    };

    OrdersBulkActionAbstract.prototype.isAllRecordsLoaded = function()
    {
        return (this.getDisplayedRecordCount() == this.getTotalRecordCount());
    };

    OrdersBulkActionAbstract.prototype.getDisplayedRecordCount = function()
    {
        var dataTable = this.getDataTableElement().dataTable();
        return dataTable.fnGetData().length;
    };

    OrdersBulkActionAbstract.prototype.getTotalRecordCount = function()
    {
        var dataTable = this.getDataTableElement().dataTable();
        return dataTable.fnSettings().fnRecordsTotal();
    };

    OrdersBulkActionAbstract.prototype.getFilterData = function()
    {
        var data = {};
        $("#filters :input[name]").each(function() {
            var value = $.trim($(this).val());
            if (!value.length) {
                return;
            }
            var name = $(this).attr("name");
            if (name == 'more[]') {
                return true; // continue
            }

            // Convert 'xfield' to 'filter[xfield]' and 'yfield[subfield]' to 'filter[yfield][subfield]'
            name = name.replace(/^(.*?)(\[.*\])?$/g, "filter[$1]$2");
            // Special case for 'zfield[]' as they all have the same name and would overwrite each other. Convert to a proper array.
            if (name.match(/\[\]$/)) {
                name = name.replace(/\[\]$/, '');
                if (typeof data[name] == 'undefined') {
                    data[name] = [];
                }
                data[name].push(value);
                return true; // continue
            }

            data[name] = value;
        });
        return data;
    };

    OrdersBulkActionAbstract.prototype.getOrderData = function()
    {
        return {"orders": this.getOrders()};
    };

    OrdersBulkActionAbstract.prototype.getOrders = function()
    {
        if (this.getElement().parent().parent().hasClass('disabled')) {
            return [];
        }
        var orders = this.getElement().data('orders');
        if (!orders) {
            orders = bulkActionService.getSelectedOrders();
        }
        return orders;
    };

    OrdersBulkActionAbstract.prototype.setFilterId = function(filterId)
    {
        $(OrdersBulkActionAbstract.SELECTOR_FILTER_BAR).data('id', filterId);
    };

    OrdersBulkActionAbstract.prototype.saveFilterOnly = function()
    {
        var self = this;
        var data = this.getDataToSubmit();
        if (data.filterId || data.orders) {
            return;
        }
        this.getAjaxRequester().sendRequest(
            OrdersBulkActionAbstract.URL_SAVE_FILTER, data, function(response)
            {
                self.setFilterId(response.filterId);
            }
        );
        return this;
    };

    return OrdersBulkActionAbstract;
});