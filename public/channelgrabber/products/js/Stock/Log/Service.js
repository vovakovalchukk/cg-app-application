define(["Clipboard"], function(Clipboard)
{
    function Service(dataTable)
    {
        this.getDataTable = function()
        {
            return dataTable;
        };

        var init = function()
        {
            var self = this;
            dataTable.on('fnDrawCallback fnSetColumnVis', function()
            {
                self.setUpClipboardElements();
            });
        };
        init.call(this);
    }

    Service.SELECTOR_COPY_CELLS = 'div.stock-log-id-cell, div.stock-log-itid-cell, div.stock-log-stid-cell';

    Service.prototype.setUpClipboardElements = function()
    {
        var tableId = this.getDataTable().attr('id');
        this.getDataTable().find(Service.SELECTOR_COPY_CELLS).each(function()
        {
            var td = $(this).closest("td");
            var tr = $(td).closest("tr");
            var input = $(td).find('input:hidden');
            var selector = "#" + tableId + " tr:nth-child(" + (tr.index() + 1) + ") td:nth-child(" + (td.index() + 1) + ") span";
            new Clipboard(selector, input);
        });
    };

    return Service;
});