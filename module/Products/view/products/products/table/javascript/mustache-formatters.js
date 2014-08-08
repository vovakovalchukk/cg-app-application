$("#<?= $tableId ?>").on("renderColumn", function(event, cgmustache, template, column, data) {
    data.formatCurrency = cgmustache.formatCurrency(data);
    data.formatDateTime = cgmustache.formatDateTime(data);
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