import Tooltip from 'tooltip';
import showHideFilters from 'showHideFilters';
import filterCollection from 'Filters/js/FilterCollection';

const ordersIndex = (function ordersIndex() {
    return {
        init: ({
           pdfExportOptions,
           filterNames
        }) => {
            var orderPage = new showHideFilters();
            filterCollection.setFilters(filterNames);
            createToolTip();
            renderGiftMessageTemplate();
            setupDataTableListeners();
        }
    };

    function createToolTip() {
        var buyerMessageTooltip = new Tooltip(
            "#datatable-container",
            ".buyer-message-holder",
            function() {
                return $(this).attr("title");
            }
        );
    }

    function renderGiftMessageTemplate() {
        var giftMessageTooltipTemplate = '/cg-built/orders/template/columns/giftMessage/tooltip.mustache';
        CGMustache.get().fetchTemplate(
            giftMessageTooltipTemplate,
            function(template, cgmustache) {
                var giftMessageTooltip = new Tooltip(
                    "#datatable-container",
                    ".gift-message-holder",
                    function() {
                        var giftMessages = $(this).data("gift-messages");
                        if (typeof (giftMessages) !== "object" || !giftMessages.length) {
                            return "";
                        }

                        var counter = 0;
                        var seperator = function(text, renderer) {
                            if (counter++ > 0) {
                                return renderer(text);
                            }
                            return "";
                        };

                        return cgmustache.renderTemplate(
                            template,
                            {
                                'giftMessages': giftMessages,
                                'seperator': function() {
                                    return seperator;
                                }
                            }
                        );
                    }
                );
            }
        );
    }

    function setupDataTableListeners() {
        $('#datatable-container').on('xhr', function(event, oSettings, oldJson) {
            var orderIds = {};
            oldJson.Records.forEach(function(record) {
                orderIds[record.id] = record;
            });

            var orderIdsArray = Object.keys(orderIds);
            if (!orderIdsArray.length) {
                return;
            }

            $.ajax({
                "url": '/orders/getDeferredColumnData',
                "type": 'POST',
                "data": {
                    orderIds: orderIdsArray
                },
                "dataType": 'json',
                "success": function(response) {
                    var newData = response.newData;
                    oSettings.aoData.forEach(function(record, rowIndex) {
                        var orderId = record._aData['id'];
                        if (newData[orderId] === undefined) {
                            return;
                        }
                        for (var colIndex in oSettings.aoColumns) {
                            var attrname = oSettings.aoColumns[colIndex].mData;
                            if (newData[orderId][attrname] === undefined) {
                                continue;
                            }
                            oSettings.oInstance.fnUpdate(newData[orderId][attrname], rowIndex, colIndex, false, false);
                        }
                    });
                },
                error: function(error) {
                    console.warn(error);
                }
            });
        });
    }
}());

export default ordersIndex;