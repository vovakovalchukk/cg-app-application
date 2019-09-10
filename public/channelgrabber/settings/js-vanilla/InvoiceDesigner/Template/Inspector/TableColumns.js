define([
    'InvoiceDesigner/Template/InspectorAbstract',
    'InvoiceDesigner/dragAndDropList',
    'InvoiceDesigner/Template/Storage/Table',
    'cg-mustache'
], function(
    InspectorAbstract,
    dragAndDropList,
    TableStorage,
    CGMustache
) {
    let TableColumns = function() {
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
        const targetNode = document.querySelector(TableColumns.TABLE_COLUMNS_INSPECTOR_SELECTOR);
        const columnsOnElement = element.getTableColumns();

        const list = new dragAndDropList({
            setItems: function(columns) {
                element.setTableColumns(columns)
            },
            allItems: TableStorage.getColumns()
        });
        const listNode = list.generateList(columnsOnElement.slice(), targetNode, 'drag-sort-enable');

        targetNode.append(listNode)
    };

    return new TableColumns();
});