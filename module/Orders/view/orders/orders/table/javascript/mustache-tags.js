$("#<?= $tableId ?>").on("renderColumn", function(event, cgmustache, template, column, data) {
    data.tag_url = "<?= urldecode($this->url('Orders/tag/action', ['tagAction' => '{{action}}'])) ?>";
    data.hasTag = function() {
        return function(variable, render) {
            if ($.inArray(render(variable), data.tag) >= 0) {
                return " checked=\"checked\"";
            }
        }
    };
});