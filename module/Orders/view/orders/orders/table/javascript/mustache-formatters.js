$("#<?= $tableId ?>").on("renderColumn", function(event, cgmustache, template, column, data) {
    data.formatCurrency = cgmustache.formatCurrency(data, column.mData);
    data.formatDateTime = cgmustache.formatDateTime(data, column.mData);
    data.formatItemName = function()
    {
        return function(variable, render) {
            var values = render(variable).split(/\n/);
            var itemName = "";
            for (var i = 0; i < values.length; i++) {
                if (i > 0) {
                    values[i] = "<br /><i>" + values[i] + "</i>";
                }
                itemName += values[i];
            }
            return itemName;
        };
    };
    data.formatAdditionalOrderItems = function()
    {
        return function(variable, render) {
            data.extraItems = JSON.parse(JSON.stringify(data.items));
            data.extraItems.splice(0, 1);
            return render(variable);
        };
    };
    data.order_url = Mustache.render("<?= urldecode($this->url('Orders/order', ['order' => '{{order}}'])) ?>", {order: data.id});
});
