function CourierReviewDataTable(dataTable, orderIds)
{
    this.getDataTable = function()
    {
        return dataTable;
    };

    this.getOrderIds = function()
    {
        return orderIds;
    };

    var init = function()
    {
        var self = this;
        dataTable.on('before-cgdatatable-init', function()
        {
            self.addOrderIdsToAjaxRequest()
                .addCustomSelectsToCourierAndServiceColumns();
        });
    };
    init.call(this);
}

CourierReviewDataTable.COLUMN_COURIER = 'courier';
CourierReviewDataTable.COLUMN_SERVICE = 'service';
CourierReviewDataTable.SELECTOR_COURIER_SELECT = '#courier-review-courier-select';

CourierReviewDataTable.prototype.addOrderIdsToAjaxRequest = function()
{
    var orderIds = this.getOrderIds();
    this.getDataTable().on("fnServerData", function(event, sSource, aoData, fnCallback, oSettings)
    {
        for (var count in orderIds)
        {
            aoData.push({
                'name': 'order['+count+']',
                'value': orderIds[count]
            });
        }
    });
    return this;
};

CourierReviewDataTable.prototype.addCustomSelectsToCourierAndServiceColumns = function()
{
    var self = this;
    this.getDataTable().on('renderColumn', function(event, cgmustache, template, column, data)
    {
        if (column.mData == CourierReviewDataTable.COLUMN_COURIER) {
            return self.addCustomSelectsToCourierColumn(data);
        }
        if (column.mData == CourierReviewDataTable.COLUMN_SERVICE) {
            // TODO
        }
    });
    return this;
};

CourierReviewDataTable.prototype.addCustomSelectsToCourierColumn = function(templateData)
{
    var courierSelectCopy = $(CourierReviewDataTable.SELECTOR_COURIER_SELECT).clone();
    var name = 'courier_'+templateData.orderId;
    courierSelectCopy.removeAttr('id').attr('data-element-name', name);
    $('input[type=hidden]', courierSelectCopy).attr('name', name);
    if (templateData.courier) {
        $('input[type=hidden]', courierSelectCopy).val(templateData.courier);
        $('ul li[data-value='+templateData.courier+']', courierSelectCopy).addClass('active');
    }

    templateData.courierOptions = $('<div>').append(courierSelectCopy).html();
    return this; 
};