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

        var self = this;
        var templateUrlMap = {
            collapsible: '/channelgrabber/zf2-v4-ui/templates/elements/collapsible.mustache'
        };

        CGMustache.get().fetchTemplates(templateUrlMap, async (templates, cgmustache) => {
            const list = new dragAndDropList({
                setItems: function(columns) {
                    element.setTableColumns(columns);
                },
                allItems: TableStorage.getColumns(),
                items: columnsOnElement.slice(),
                targetNode,
                listClasses: {
                    dragActive : 'invoice-designer-list-item-drag-active',
                    itemsContainer: 'drag-and-drop-list-list-item',
                    listItem: 'invoice-designer-list-item',
                    dragIcon : 'sprite sprite-drag-handle-black-24 invoice-designer-drag-icon',
                    dragContainer: 'invoice-designer-drag-icon-container',
                    deleteClass: 'sprite sprite-delete-18-black',
                    listItemInput: 'invoice-designer-drag-list-input'
                }
            });

            const listHtml = await list.generateList();
            const collapsible = cgmustache.renderTemplate(templates, {
                'display': true,
                'title': 'Table Columns',
                'id': 'table-collapsible'
            }, "collapsible", {'content': listHtml});

            const tableColumnsInspector = document.getElementById('tableColumns-inspector');
            const template = cgmustache.renderTemplate(collapsible, {}, 'tableColumn');
            tableColumnsInspector.append(document.createRange().createContextualFragment(template));

            list.initList();
        });
    };

    return new TableColumns();
});