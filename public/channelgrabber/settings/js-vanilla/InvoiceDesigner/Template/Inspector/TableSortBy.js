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
    let TableSortBy = function() {
        InspectorAbstract.call(this);

        this.setId('tableSortBy');
        this.setInspectedAttributes(['tableSortBy']);
    };

    TableSortBy.TABLE_SORTBY_COLUMNS_INSPECTOR = '#tableSortBy-inspector';

    TableSortBy.prototype = Object.create(InspectorAbstract.prototype);

    TableSortBy.prototype.hide = function() {
        this.getDomManipulator().render(TableSortBy.TABLE_SORTBY_COLUMNS_INSPECTOR, "");
    };

    TableSortBy.prototype.showForElement = function(element) {
        const targetNode = document.querySelector(TableSortBy.TABLE_SORTBY_COLUMNS_INSPECTOR);
        const tableSortBy = element.getTableSortBy();

        const templateUrlMap = {
            collapsible: '/channelgrabber/zf2-v4-ui/templates/elements/collapsible.mustache'
        };

        CGMustache.get().fetchTemplates(templateUrlMap, async (templates, cgmustache) => {
            const list = new dragAndDropList({
                setItems: function setItems(newSortBy) {
                    element.setTableSortBy(newSortBy);
                },
                id: 'table-sort-by-dnd',
                allItems: TableStorage.getColumns(),
                items: tableSortBy.slice(),
                itemLimit: 3,
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
                'title': 'Sort By',
                'id': 'sortby-collapsible'
            }, "collapsible", {'content': listHtml});

            const template = cgmustache.renderTemplate(collapsible, {}, 'tableColumn');
            targetNode.append(document.createRange().createContextualFragment(template));

            list.initList();
        });
    };

    return new TableSortBy();
});