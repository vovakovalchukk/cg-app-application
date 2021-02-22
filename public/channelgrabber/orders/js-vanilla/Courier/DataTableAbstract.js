function CourierDataTableAbstract(dataTable, orderIds, templateMap)
{
    var orderParity = 'even';
    var rowGroup = null;
    var templates = {};

    var bulkActionMap = {
        termsOfDelivery: "doThis"
    };

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

    this.getTemplateMap = function()
    {
        console.log('MAP');
        console.log(templateMap);
        return templateMap;
    };

    this.getTemplate = function(type)
    {
        console.log('TEMP');
        console.log(templates);
        if (templates.hasOwnProperty(type)) {
            return templates[type];
        }
        return null;
    };

    this.addTemplate = function(type, template)
    {
        templates[type] = template;
        return this;
    };

    var init = function()
    {
        this.alternateOrderRowColours()
            .addBulkActionRows()
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
            $(nRow).addClass('courier-order-row').attr('id', 'courier-order-row_'+aData.orderId);
            orderParity = (orderParity == 'even' ? 'odd' : 'even');
            self.setOrderParity(orderParity);
        } else if (aData.parcelRow) {
            $(nRow).addClass('courier-parcel-row').attr('id', 'courier-parcel-row_'+aData.orderId+'_'+aData.parcelNumber);
        } else if (aData.itemRow) {
            $(nRow).addClass('courier-item-row').attr('id', 'courier-item-row_'+aData.itemId);
        }
        var className = orderParity+'-order-row';
        $(nRow).addClass(className);
    });
    return this;
};

CourierDataTableAbstract.prototype.addBulkActionRows = function()
{
    var self = this;
    this.getDataTable().on('fnDrawCallback', function(event, settings)
    {
        // console.log(settings);

        var oData = settings.aoData[0];
        var nRow = oData.nTr;


        // var columns = settings.aoColumns;

        // console.log(columns);

        // var tr = '<tr class="courier-bulk-action-row">';
        var tr = document.createElement('tr');
        $(tr).addClass('courier-bulk-action-row');

        for (var index in settings.aoColumns) {

            var td = document.createElement('td');
            $(td).addClass(settings.aoColumns[index].nTh.getAttribute('class'));

            $(tr).append($(td));
            // var td = '<td></td>';
            // td.
            //tr += '<td></td>';
            // var classes = settings.aoColumns[index].nTh.getAttribute('class')

            console.log(settings.aoColumns[index].templateId);
        }

        // <td colSpan="' + $(nRow).find('td').length + '">HELLO WORLD</td>

        // var tr = '<tr class="courier-bulk-action-row">';

        // var t = $(nRow).find('thead').find('tr.row').children();

        // console.log(t);

        // tr += '</tr>';

        // console.log(self.columns());




        $(nRow).before(tr);
        // t.row.add();

        // for (var index in settings.aoData) {
        //     var oData = settings.aoData[index];
        //     var aData = oData._aData;
        //     var nRow = oData.nTr;
        //     var rowGroup = self.getRowGroup();
        //     if (!aData.group || !aData.orderRow || aData.group == rowGroup) {
        //         continue;
        //     }
        //     $(nRow).before('<tr class="courier-bulk-action-row"><td colspan="' + $(nRow).find('td').length + '">HELLO WORLD</td></tr>');
        //     self.setRowGroup(aData.group);
        // }
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

CourierDataTableAbstract.prototype.addCustomSelectToServiceColumn = function(templateData, cgMustache, name)
{
    if (!templateData.services) {
        return;
    }

    var self = this;
    this.fetchTemplate('select', cgMustache, function(template)
    {
        var data = {
            id: 'courier-service-options-select-' + templateData.orderId,
            name: name || 'orderData[' + templateData.orderId + '][service]',
            class: 'required courier-service-select',
            searchField: true,
            options: []
        };
        for (var code in templateData.services) {
            data.options.push({
                title: templateData.services[code],
                value: code,
                selected: (code == templateData.service)
            });
        }
        var html = cgMustache.renderTemplate(template, data);

        var $html = $(html);
        $html.find('.custom-select').addClass('courier-service-custom-select');
        // html() calls innerHtml which drops the outer-most element so wrap it in a throw-away first
        html = $html.wrap('<div></div>').html();

        // If there's only one option don't bother with the select, just show it
        if (Object.keys(templateData.services).length == 1) {
            var serviceCode = Object.keys(templateData.services).pop();
            var serviceName = templateData.services[code];
            html = self.renderSingleService(html, serviceCode, serviceName, templateData.orderId);
        }

        templateData.serviceOptions = html;
    }, true);
};


CourierDataTableAbstract.prototype.renderSingleService = function(selectHtml, serviceCode, serviceName, orderId)
{
    // Keep the input, copy it to the new element
    var input = $('input[type=hidden]', selectHtml);
    input.val(serviceCode);

    return $('<div><span>'+serviceName+'</span></div>')
        .append(input)
        .html();
};

CourierDataTableAbstract.prototype.fetchTemplate = function (templateName, cgMustache, callback, synchronous)
{
    var template = this.getTemplate(templateName);
    if (template) {
        callback(template, cgMustache);
        return;
    }
    cgMustache.fetchTemplate(this.getTemplateMap()[templateName], function(template)
    {
        callback(template, cgMustache);
    }, synchronous);
};