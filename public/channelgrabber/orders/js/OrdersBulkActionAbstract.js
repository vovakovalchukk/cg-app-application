define(['BulkActionAbstract'], function(BulkActionAbstract)
{
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

    OrdersBulkActionAbstract.SELECTOR_FILTER_BAR = '#filter';

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
        var dataTable = this.getDataTableElement().dataTable();
        return (dataTable.fnSettings().fnRecordsTotal() == dataTable.fnGetData().length);
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
            name = name.replace(/^(.*?)(\[.*\])?$/g, "filter[$1]$2");
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
        var orders = this.getElement().data('orders');
        if (!orders) {
            orders = this.getDataTableElement().cgDataTable("selected", ".checkbox-id");
        }
        return orders;
    };

    OrdersBulkActionAbstract.prototype.setFilterId = function(filterId)
    {
        $(OrdersBulkActionAbstract.SELECTOR_FILTER_BAR).data('id', filterId);
    };

    return OrdersBulkActionAbstract;
});