define([
    'InvoiceDesigner/Template/InspectorAbstract',
    'InvoiceDesigner/dragAndDropList',
    'InvoiceDesigner/Template/Storage/Table',
    'InvoiceDesigner/Template/Inspector/Helpers/dragAndDrop',
    'cg-mustache'
], function(
    InspectorAbstract,
    dragAndDropList,
    TableStorage,
    dragAndDropHelper,
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
        const tableSortBy = element.getTableSortBy().sort((a, b) => {
            return a.position - b.position;
        });
        const listClasses = dragAndDropHelper.getDefaultDragAndDropCSSClasses();

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
                listClasses
            });

            const listHtml = await list.generateList();
            const collapsible = cgmustache.renderTemplate(templates, {
                'display': true,
                'title': 'Sort By',
                'id': 'table-sortby-collapsible'
            }, "collapsible", {'content': listHtml});

            const template = cgmustache.renderTemplate(collapsible, {}, 'tableColumn');
            targetNode.append(document.createRange().createContextualFragment(template));

            list.initList();
        });
    };

    return new TableSortBy();
});