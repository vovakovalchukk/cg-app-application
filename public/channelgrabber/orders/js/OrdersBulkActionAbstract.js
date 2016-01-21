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

    OrdersBulkActionAbstract.prototype = Object.create(BulkActionAbstract.prototype);

    OrdersBulkActionAbstract.prototype.getDataToSubmit = function()
    {
        if ($('#filter').data('id')) {
            return {"filterId": $('#filter').data('id')};
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
        return (this.getDataTableElement().fnSettings().fnRecordsTotal() == this.getDataTableElement().fnGetData().length);
    };

    OrdersBulkActionAbstract.prototype.getFilterData = function()
    {
        var data = [];
        $("#filters :input[name]").each(function() {
            var value = $.trim($(this).val());
            if (!value.length) {
                return;
            }
            var name = $(this).attr("name").replace(/^(.*?)(\[.*\])?$/g, "filter[$1]$2");

            data.push({
                "name": name,
                "value": value
            });
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

    return OrdersBulkActionAbstract;
});