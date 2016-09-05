function CourierDataTableAbstract(dataTable, orderIds)
{
    var orderParity = 'even';
    var rowGroup = null;

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

    this.getRowGroup = function()
    {
        return rowGroup;
    };

    this.setRowGroup = function(newRowGroup)
    {
        rowGroup = newRowGroup;
        return this;
    };

    var init = function()
    {
        this.alternateOrderRowColours()
            .addGroupRows();
    }
    init.call(this);
}

CourierDataTableAbstract.prototype.addOrderIdsToAjaxRequest = function()
{
    var self = this;
    var orderIds = this.getOrderIds();
    this.getDataTable().on("fnServerData", function(event, sSource, aoData, fnCallback, oSettings)
    {
        self.setOrderParity('even')
            .setRowGroup(null);
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
        if ($(nRow).hasClass('even-order-row') || $(nRow).hasClass('odd-order-row')) {
            return;
        }
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
    return this;
};

CourierDataTableAbstract.prototype.addGroupRows = function()
{
    var self = this;
    this.getDataTable().on('fnDrawCallback', function(event, settings)
    {
        for (var index in settings.aoData) {
            var oData = settings.aoData[index];
            var aData = oData._aData;
            var nRow = oData.nTr;
            var rowGroup = self.getRowGroup();
            if (!aData.group || !aData.orderRow || aData.group == rowGroup) {
                continue;
            }
            $(nRow).before('<tr class="courier-group-row"><td colspan="' + $(nRow).find('td').length + '">' + aData.group + '</td></tr>');
            self.setRowGroup(aData.group);
        }
    });
    return this;
};