require.config({
    paths: {
        enableChannel: "<?= Settings\Module::PUBLIC_FOLDER ?>js/enableChannel"
    }
});

require(
    ["enableChannel"],
    function(enableChannel) {
        var ajaxCheckbox = enableChannel(
            n,
            "#<?= $tableId ?>",
            "input.toggle",
            {
                url: "<?= urldecode($this->url($route, ['account' => '{{id}}', 'type' => '{{type}}'])) ?>"
            },
            {
                info: "<?= $this->translate('Updating Sales Channel Status') ?>",
                error: "<?= $this->translate('Updating Sales Channel Status') ?>",
                success: "<?= $this->translate('Sales Channel Status Updated') ?>"
            }
        );

        ajaxCheckbox.bindAjaxResponse(function(event, data) {
            if (!data.account) {
                return;
            }

            var dataTable = $(ajaxCheckbox.getBaseSelector()).dataTable();
            var row = $(this).closest("tr");
            if (!row.length) {
                return;
            }
            var position = dataTable.fnGetPosition(row[0]);

            dataTable.fnUpdate(data.account, position, undefined, false, false);
        });
    }
);