function CourierDataTableAbstract(dataTable, orderIds)
{
    var orderParity = 'even';

    this.getDataTable = function()
    {
        return dataTable;
    };

    this.getOrderIds = function()
    {
        return orderIds;
    };

    this.getOrderParity = function()
    {
        return orderParity;
    };

    this.setOrderParity = function(newOrderParity)
    {
        orderParity = newOrderParity;
        return this;
    };

    var init = function()
    {
        this.alternateOrderRowColours();
    }
    init.call(this);
}

CourierDataTableAbstract.prototype.addOrderIdsToAjaxRequest = function()
{
    var self = this;
    var orderIds = this.getOrderIds();
    this.getDataTable().on("fnServerData", function(event, sSource, aoData, fnCallback, oSettings)
    {
        self.setOrderParity('even');
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

CourierDataTableAbstract.prototype.cloneCustomSelectElement = function(templateSelector, cloneName, cloneClass, cloneSelectValue)
{
    var selectCopy = $(templateSelector).clone();
    selectCopy.removeAttr('id').attr('data-element-name', cloneName);
    if (cloneClass) {
        selectCopy.addClass(cloneClass);
    }
    $('input[type=hidden]', selectCopy).attr('name', cloneName);
    if (cloneSelectValue) {
        $('input[type=hidden]', selectCopy).val(cloneSelectValue);
        $('ul li[data-value="'+cloneSelectValue+'"]', selectCopy).addClass('active');
    }
    return selectCopy;
};

CourierDataTableAbstract.prototype.alternateOrderRowColours = function()
{
    var self = this;
    this.getDataTable().on('fnRowCallback', function(event, nRow, aData)
    {
        var orderParity = self.getOrderParity();
        if (aData.orderRow) {
            $(nRow).addClass('courier-order-row');
            orderParity = (orderParity == 'even' ? 'odd' : 'even');
            self.setOrderParity(orderParity);
        } else if (aData.parcelRow) {
            $(nRow).addClass('courier-parcel-row');
        }
        var className = orderParity+'-order-row';
        $(nRow).addClass(className);
    });
};