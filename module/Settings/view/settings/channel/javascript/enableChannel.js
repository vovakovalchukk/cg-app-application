require(
    ["ajaxCheckbox", "mustache"],
    function(AjaxCheckbox, Mustache) {
        var ajaxCheckbox = new AjaxCheckbox(n, "#<?= $tableId ?>", "input.toggle", {
            url: "<?= urldecode($this->url($route, ['channel' => '{{id}}'])) ?>"
        });

        ajaxCheckbox.bindAjax(function() {
            ajaxCheckbox.getNotifications().notice("<?= $this->translate('Updating Sales Channel Status') ?>");
        });

        ajaxCheckbox.bindAjax(function(event, ajaxOptions) {
            var id = $(this).data("id");
            if (!id) {
                return;
            }
            ajaxOptions.url = Mustache.render(ajaxOptions.url, {id: id});
        });

        ajaxCheckbox.bindAjaxError(function() {
            $(this).prop("checked", !$(this).is(":checked"));
        });

        ajaxCheckbox.bindAjaxResponse(function(event, data) {
            var notifications = ajaxCheckbox.getNotifications();

            if (!data.updated) {
                notifications.error("<?= $this->translate('Updating Sales Channel Status') ?>");
                return;
            }

            notifications.success("<?= $this->translate('Sales Channel Status Updated') ?>");
        });
    }
);