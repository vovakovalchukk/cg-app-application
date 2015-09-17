function CourierDataTableAbstract(dataTable, orderIds)
{
    this.getDataTable = function()
    {
        return dataTable;
    };

    this.getOrderIds = function()
    {
        return orderIds;
    };
}

CourierDataTableAbstract.prototype.addOrderIdsToAjaxRequest = function()
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
}