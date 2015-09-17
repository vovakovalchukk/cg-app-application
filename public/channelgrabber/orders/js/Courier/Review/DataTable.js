function CourierReviewDataTable(dataTable, orderIds)
{
    CourierDataTableAbstract.call(this, dataTable, orderIds);

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
CourierReviewDataTable.SELECTOR_SERVICE_SELECT_PREFIX = '#courier-service-select-';

CourierReviewDataTable.prototype = Object.create(CourierDataTableAbstract.prototype);

CourierReviewDataTable.prototype.addCustomSelectsToCourierAndServiceColumns = function()
{
    var self = this;
    this.getDataTable().on('renderColumn', function(event, cgmustache, template, column, data)
    {
        if (column.mData == CourierReviewDataTable.COLUMN_COURIER) {
            return self.addCustomSelectsToCourierColumn(data);
        }
        if (column.mData == CourierReviewDataTable.COLUMN_SERVICE) {
            return self.addCustomSelectsToServiceColumn(data);
        }
    });
    return this;
};

CourierReviewDataTable.prototype.addCustomSelectsToCourierColumn = function(templateData)
{
    var name = 'courier_'+templateData.orderId;
    var templateSelector = CourierReviewDataTable.SELECTOR_COURIER_SELECT;
    var courierSelectCopy = this.cloneCustomSelectElement(
        templateSelector, name, 'courier-courier-custom-select', templateData.courier
    );
    templateData.courierOptions = $('<div>').append(courierSelectCopy).html();
    return this; 
};

CourierReviewDataTable.prototype.addCustomSelectsToServiceColumn = function(templateData)
{
    if (!templateData.courier) {
        return;
    }
    var name = 'service_'+templateData.orderId;
    var templateSelector = CourierReviewDataTable.SELECTOR_SERVICE_SELECT_PREFIX+templateData.courier;
    var serviceSelectCopy = this.cloneCustomSelectElement(
        templateSelector, name, 'courier-service-custom-select', templateData.service
    );
    templateData.serviceOptions = $('<div>').append(serviceSelectCopy).html();
    return this;
};