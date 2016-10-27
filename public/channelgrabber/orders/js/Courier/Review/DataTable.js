function CourierReviewDataTable(dataTable, orderIds, templateMap)
{
    CourierDataTableAbstract.call(this, dataTable, orderIds, templateMap);

    var init = function()
    {
        var self = this;
        dataTable.on('before-cgdatatable-init', function()
        {
            self.addOrderIdsToAjaxRequest()
                .addCustomSelectsToServiceColumn();
        });
    };
    init.call(this);
}

CourierReviewDataTable.COLUMN_SERVICE = 'service';
CourierReviewDataTable.SELECTOR_SERVICE_SELECT_PREFIX = '#courier-service-select-';

CourierReviewDataTable.prototype = Object.create(CourierDataTableAbstract.prototype);

CourierReviewDataTable.prototype.addCustomSelectsToServiceColumn = function(templateData)
{
    var self = this;
    this.getDataTable().on('renderColumn', function(event, cgMustache, template, column, data)
    {
        if (column.mData != CourierReviewDataTable.COLUMN_SERVICE || !data.orderRow) {
            return;
        }
        var name = 'service_'+data.orderId;
        self.addCustomSelectToServiceColumn(data, cgMustache, name);
    });
    return this;
};
