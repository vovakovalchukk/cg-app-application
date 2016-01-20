define([], function()
{
    function BulkActionAbstract()
    {
        this.getElement = function()
        {
            return $(this);
        };

        this.getDataTableElement = function()
        {
            return $('#' + this.getElement().data('datatable'));
        };
    }

    BulkActionAbstract.prototype.getDataToSubmit = function()
    {
        if ($('#filter').data('id')) {
            return {"filterId": $('#filter').data('id')};
        }
        if (this.isAllSelected() && !this.isAllRecordsLoaded()) {
            return this.getFilterData();
        }
        return this.getOrderData();
    };

    BulkActionAbstract.prototype.isAllSelected = function()
    {
        var selectAllDomId = this.getElement().data('datatable') + '-select-all';
        return $('#'+selectAllDomId).is(':checked');
    };

    BulkActionAbstract.prototype.isAllRecordsLoaded = function()
    {
        return (this.getDataTableElement().fnSettings().fnRecordsTotal() == this.getDataTableElement().fnGetData().length);
    };

    BulkActionAbstract.prototype.getFilterData = function()
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

    BulkActionAbstract.prototype.getOrderData = function()
    {
        var orders = this.getElement().data('orders');
        if (!orders) {
            orders = this.getDataTableElement().cgDataTable("selected", ".checkbox-id");
        }
        return {"orders": orders};
    };

    return BulkActionAbstract;
});