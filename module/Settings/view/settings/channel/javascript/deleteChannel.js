
require(
    ["ajaxLink"],
    function(AjaxLink) {
        var ajaxLink = new AjaxLink(n, "#<?= $tableId ?>", ".manage .delete");
        $(ajaxLink).bind("clicked", function(event, clicked) {
            this.getNotifications().notice("<?= $this->translate('Deleting Channel') ?>");
        });
        $(ajaxLink).bind("response", function(event, json) {
            if (!json.deleted && json.exception) {
                this.getNotifications().error(json.exception);
                return;
            } else if (!json.deleted) {
                this.getNotifications().error("<?= $this->translate('An error has occurred, please try again') ?>");
                return;
            }

            this.getNotifications().success("<?= $this->translate('Channel Deleted') ?>");
            $("#<?= $tableId ?>").cgDataTable("redraw");
        });
    }
);