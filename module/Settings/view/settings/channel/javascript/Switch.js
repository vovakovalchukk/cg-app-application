require.config({
    paths: {
        ajaxSwitch: "<?= Settings\Module::PUBLIC_FOLDER ?>js/ajaxSwitch"
    }
});

require(
        ["ajaxSwitch", "popup/confirm"],
        function (ajaxSwitch, Confirm) {
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
            }
            , true

                    );

            ajaxCheckbox.bindAjax(function (event, ajaxOptions, input)
            {

                var toggleID = this.id;
                var idParts = toggleID.split("-");
                if (idParts[0] !== "stockManagement") {
                    ajaxCheckbox.saveStatus(ajaxOptions, input);
                    return;
                }
                //get status of toggle switch before user interacts

                if (!$(this).is(':checked')) {
                    input.prop("disabled", false);
                    ajaxCheckbox.saveStatus(ajaxOptions, input);
                    return;
                } 



                var message = "<strong>Stock Management Confirmation</strong><br/>";
                //message +="<ul>";
                message += "<p><strong>1.</strong> If any of your products have missing SKUs we will not be able to manage the stock for these products.<br/>";
                message += "We ignore any product without a SKU.</p>";
                message += "<p><strong>2.</strong> If your SKUs are incorrect/duplicated and/or do not match across your channels, we will not be able to manage\n";
                message += "the stock effectively for these products.</p>";
                message += "<p><strong>3.</strong> If any of your products have zero or negative stock quantities, listings with that product will be ended or\n";
                message += "marked out of stock and they will no longer be able to be purchased</p>";
                message += "<p><strong>4.</strong> Once stock management is turned on, OrderHub will become your main stock controller,";
                message += "any manual stock changes should be done through OrderHub and not directly on the Marketplace or Webstore.";
                message += "If you use any other stock control system, you should disable it as having more than one active can cause\
                unpredictable results.</p>";
                message += "<p><strong>5.</strong> If you are unsure or have questions about any of the above, please call us on 0161 711 0238 to discuss.</p>";
                message += "<p><strong><em>Do you wish to continue?</em></strong></p>";



                var confirm = new Confirm(message, function (response) {

              
                    if (response == "Yes") {
                        ajaxCheckbox.saveStatus(ajaxOptions, input);
                    }


                    if (response == "No") {
                       $('#'+toggleID).attr("checked", false);
                       n.clearNotifications($("#main-notifications"));
                       input.prop("disabled", false);
                    }

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