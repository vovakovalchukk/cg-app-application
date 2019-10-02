define([
    'InvoiceDesigner/Template/InspectorAbstract',
    'InvoiceDesigner/dragAndDropList',
    'InvoiceDesigner/Template/Storage/Table',
    'InvoiceDesigner/Template/Element/Helpers/OrderTable',
    'cg-mustache'
], function(
    InspectorAbstract,
    dragAndDropList,
    TableStorage,
    orderTableHelper,
    CGMustache
) {
    let TableTotals = function() {
        InspectorAbstract.call(this);

        this.setId('tableTotals');
        this.setInspectedAttributes(['tableTotals']);
    };

    TableTotals.TABLE_COLUMNS_INSPECTOR_SELECTOR = '#tableTotals-inspector';

    TableTotals.prototype = Object.create(InspectorAbstract.prototype);

    TableTotals.prototype.hide = function() {
        this.getDomManipulator().render(TableTotals.TABLE_COLUMNS_INSPECTOR_SELECTOR, "");
    };

    TableTotals.prototype.showForElement = function(element) {
        console.log('in TableTotals showForElement ', element);


    };

    return new TableTotals();
});