$("#<?= $id ?>").bulkActions("set", "<?= $action ?>", function(event) {
    var orders = [<?= isset($order) ? '"' . $order->getId() . '"' : '' ?>];
    $("#datatable .order-id:checked").each(function() {
        orders.push($(this).val());
        });

    if (!orders.length) {
        return;
    }

    var tag = <?= isset($tag) ? '"' . $tag . '"' : '$.trim(window.prompt("Name of Tag:", "tag"))' ?>;
    if (!tag.length) {
        return;
    }

    $("#datatable_processing").css("visibility", "visible");
    $.ajax({
        'url': "<?= $this->url('Orders/tag') ?>",
        'type': "POST",
        'data': {
            'tag': tag,
            'orders': orders
        },
        'complete': function() {
            $("#datatable").cgDataTable("redraw");
        }
    });
});