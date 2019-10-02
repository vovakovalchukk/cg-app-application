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
    let TableColumns = function() {
        InspectorAbstract.call(this);

        this.setId('tableColumns');
        this.setInspectedAttributes(['tableColumns']);
    };

    TableColumns.TABLE_COLUMNS_INSPECTOR_ID = 'tableColumns-inspector';

    TableColumns.prototype = Object.create(InspectorAbstract.prototype);

    TableColumns.prototype.hide = function() {
        this.getDomManipulator().render(`#${TableColumns.TABLE_COLUMNS_INSPECTOR_ID}`, "");
    };

    TableColumns.prototype.showForElement = function(element) {
        const columnsOnElement = element.getTableColumns();

        const templateUrlMap = {
            collapsible: '/channelgrabber/zf2-v4-ui/templates/elements/collapsible.mustache'
        };

        CGMustache.get().fetchTemplates(templateUrlMap, async (templates, cgmustache) => {
            const list = new dragAndDropList({
                setItems: function setItems(columns) {
                    const currentTableCells = element.getTableCells().slice();
                    const reducedTableCells = removeAssociatedTableCellsFromRemovedTableColumns(currentTableCells, columns);
                    const newTableCells = addNewTableCellsForNewlyAddedColumns(columns, reducedTableCells);
                    element.setTableCells(newTableCells);
                    element.setTableColumns(columns);
                },
                allItems: TableStorage.getColumns(),
                items: columnsOnElement.slice(),
                id: 'table-columns-dnd',
                renderTextInput: true,
                listClasses: {
                    dragActive: 'invoice-designer-list-item-drag-active',
                    itemsContainer: 'drag-and-drop-list-list-item',
                    listItem: 'invoice-designer-list-item',
                    dragIcon: 'sprite sprite-drag-handle-black-24 invoice-designer-drag-icon',
                    dragContainer: 'invoice-designer-drag-icon-container',
                    deleteClass: 'sprite sprite-delete-18-black',
                    addIcon: 'invoice-designer-drag-list-add-icon sprite sprite-plus-18-black',
                    listItemInput: 'invoice-designer-drag-list-input'
                }
            });

            const listHtml = await list.generateList();
            const collapsible = cgmustache.renderTemplate(templates, {
                'display': true,
                'title': 'Table Columns',
                'id': 'table-collapsible'
            }, "collapsible", {'content': listHtml});

            const tableColumnsInspector = document.getElementById(TableColumns.TABLE_COLUMNS_INSPECTOR_ID);
            const template = cgmustache.renderTemplate(collapsible, {}, 'tableColumn');
            tableColumnsInspector.append(document.createRange().createContextualFragment(template));

            list.initList();
        });
    };

    return new TableColumns();

    function removeAssociatedTableCellsFromRemovedTableColumns(currentTableCells, columns) {
        return currentTableCells.filter(tableCell => (
            !!columns.find(column => (column.id === tableCell.column))
        ));
    }
    function addNewTableCellsForNewlyAddedColumns(columns, reducedTableCells) {
        const newColumns = columns.filter(column => (
            !reducedTableCells.find(cell => (
                cell.column === column.id
            ))
        ));
        const createdCells = orderTableHelper.formatDefaultTableCellsFromColumns(newColumns);
        const newTableCells = reducedTableCells.concat(createdCells);
        return newTableCells;
    }
});