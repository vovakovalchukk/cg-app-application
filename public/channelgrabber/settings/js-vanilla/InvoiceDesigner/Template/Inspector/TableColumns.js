define([
    'InvoiceDesigner/Template/InspectorAbstract',
    'InvoiceDesigner/dragAndDropList',
    'cg-mustache'
], function(
    InspectorAbstract,
    dragAndDropList,
    CGMustache
) {
    var TableColumns = function() {
        InspectorAbstract.call(this);

        this.setId('tableColumns');
        this.setInspectedAttributes(['tableColumns']);
    };

    TableColumns.TABLE_COLUMNS_INSPECTOR_SELECTOR = '#tableColumns-inspector';

    TableColumns.prototype = Object.create(InspectorAbstract.prototype);

    TableColumns.prototype.hide = function() {
        this.getDomManipulator().render(TableColumns.TABLE_COLUMNS_INSPECTOR_SELECTOR, "");
    };

    TableColumns.prototype.showForElement = function(element) {
        const targetNode = document.getElementById(TableColumns.TABLE_COLUMNS_INSPECTOR_SELECTOR.substring(1, TableColumns.TABLE_COLUMNS_INSPECTOR_SELECTOR.length));
        const tableColumns = element.getTableColumns();

        const list = new dragAndDropList(function(columns) {
            element.setTableColumns(columns)
        });
        const listNode = list.generateList(tableColumns.slice(), targetNode, 'drag-sort-enable');

        targetNode.append(listNode)
    };

    return new TableColumns();
});