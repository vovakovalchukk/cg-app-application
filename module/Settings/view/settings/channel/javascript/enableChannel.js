require(
    ["ajaxCheckbox"],
    function(AjaxCheckbox) {
        var ajaxCheckbox = new AjaxCheckbox(n, "#<?= $tableId ?>", "input.toggle", {});

        ajaxCheckbox.bindAjax(function() {
            ajaxCheckbox.getNotifications().notice("<?= $this->translate('Updating Sales Channel Status') ?>");
        });

        ajaxCheckbox.bindAjaxResponse(function(data) {

        });
    }
);