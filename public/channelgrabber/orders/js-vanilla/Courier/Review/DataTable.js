function CourierReviewDataTable(dataTable, orderIds)
{
    CourierDataTableAbstract.call(this, dataTable, orderIds);

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
    this.getDataTable().on('renderColumn', function(event, cgmustache, template, column, data)
    {
        if (column.mData != CourierReviewDataTable.COLUMN_SERVICE || !data.orderRow) {
            return;
        }
        var name = 'service_'+data.orderId;
        var templateSelector = CourierReviewDataTable.SELECTOR_SERVICE_SELECT_PREFIX+data.courier;
        var serviceSelectCopy = self.cloneCustomSelectElement(
            templateSelector, name, 'courier-service-custom-select', data.service
        );
        if ($(serviceSelectCopy).is(".disabled")) {
            $(serviceSelectCopy).removeAttr('class').html(function() {
                var input = $('input[type=hidden]', this);
                var selected = $('.custom-select-item.active', this);
                return $('<div></div>').text(selected.text()).append(input.val(selected.attr('data-value')));
            });
        }
        data.serviceOptions = $('<div>').append(serviceSelectCopy).html();
    });
    return this;
};
