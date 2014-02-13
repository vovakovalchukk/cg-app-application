$("#<?= $tableId ?>").on("renderColumn", function(event, cgmustache, template, column, data) {
    data.tag_url = "<?= $this->url('Orders/tag') ?>";
    data.hasTag = function() {
        return function(variable, render) {
            if ($.inArray(render(variable), data.tag) >= 0) {
                return " checked=\"checked\"";
            }
        }
    };
});