require.config({
    paths: {
        ajaxSwitch: "<?= Settings\Module::PUBLIC_FOLDER ?>js/ajaxSwitch"
    }
});

require(
    ["ajaxSwitch"],
    function(ajaxSwitch) {
        var ajaxCheckbox = ajaxSwitch(
            n,
            "#<?= $tableId ?>",
            ".<?= $switchClass; ?> input.toggle",
            {
                url: "<?= urldecode($this->url($route, ['account' => '{{id}}', 'type' => '{{type}}'])) ?>"
            },
            {
                info: "<?= $this->translate('Updating Sales Channel <?= $switchType ?>') ?>",
                error: "<?= $this->translate('Failed To Update Sales Channel <?= $switchType ?>') ?>",
                success: "<?= $this->translate('Sales Channel <?= $switchType ?> Updated') ?>"
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