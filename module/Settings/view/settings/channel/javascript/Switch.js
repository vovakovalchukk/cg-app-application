require.config({
    paths: {
        ajaxSwitch: "<?= Settings\Module::PUBLIC_FOLDER ?>js/ajaxSwitch"
    }
});

require(
    ["ajaxSwitch", "popup/confirm","cg-mustache"],
    function (ajaxSwitch, Confirm, CGMustache) {
        var ajaxCheckbox = ajaxSwitch(
            n,
            "#<?= $tableId ?>",
            ".<?= $switchClass; ?> input.toggle",
            {
                url: "<?= urldecode($this->url($route, ['account' => '{{id}}', 'type' => '{{type}}'])) ?>"
            },
            {
                info: "<?= $this->translate('Updating Channel <?= $switchType ?>') ?>",
                error: "<?= $this->translate('Failed To Update Channel <?= $switchType ?>') ?>",
                success: "<?= $this->translate('Channel <?= $switchType ?> Updated') ?>"
            },
            true
        );

        ajaxCheckbox.bindAjax(function (event, ajaxOptions, input){
            var toggleID = this.id;
            var idParts = toggleID.split("-");
            if (idParts[0] !== "stockManagement") {
                ajaxCheckbox.saveStatus(ajaxOptions, input);
                return;
            }
            if (!$(this).is(':checked')) {
                input.prop("disabled", false);
                ajaxCheckbox.saveStatus(ajaxOptions, input);
                return;
            }
            var templateUrlMap = {
                message: '<?= Settings\Module::PUBLIC_FOLDER ?>template/Messages/stockManagementEnableMessage.mustache'
            };
    
            CGMustache.get().fetchTemplates(templateUrlMap, function(templates, cgmustache){
                var messageHTML = cgmustache.renderTemplate(templates, {}, "message");
                var confirm = new Confirm(messageHTML, function (response) {
                    if (response == "Yes") {
                        ajaxCheckbox.saveStatus(ajaxOptions, input);
                    }else {
                       $('#'+toggleID).attr("checked", false);
                       n.clearNotifications($("#main-notifications"));
                       input.prop("disabled", false);
                    }
                });
            });
        });

        ajaxCheckbox.bindAjaxResponse(function (event, data) {
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