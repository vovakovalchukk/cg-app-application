define([
    'InvoiceDesigner/Template/InspectorAbstract',
    'InvoiceDesigner/dragAndDropList',
    'InvoiceDesigner/Template/Storage/Table',
    'InvoiceDesigner/Template/Element/Helpers/OrderTable',
    'InvoiceDesigner/Template/Inspector/Helpers/dragAndDrop',
    'cg-mustache'
], function(
    InspectorAbstract,
    dragAndDropList,
    TableStorage,
    orderTableHelper,
    dragAndDropHelper,
    CGMustache
) {
    let TableTotals = function() {
        InspectorAbstract.call(this);

        this.setId('tableTotals');
        this.setInspectedAttributes(['tableTotals']);
    };

    TableTotals.TABLE_TOTALS_INSPECTOR_ID = 'tableTotals-inspector';

    TableTotals.prototype = Object.create(InspectorAbstract.prototype);

    TableTotals.prototype.hide = function() {
        this.getDomManipulator().render(`#${TableTotals.TABLE_TOTALS_INSPECTOR_ID}`, "");
    };

    TableTotals.prototype.showForElement = function(element) {
        const tableTotals = element.getTableTotals();
        const allTableTotals = TableStorage.getTableTotals();
        const listClasses = dragAndDropHelper.getDefaultDragAndDropCSSClasses();

        const templateUrlMap = {
            collapsible: '/channelgrabber/zf2-v4-ui/templates/elements/collapsible.mustache'
        };
        CGMustache.get().fetchTemplates(templateUrlMap, async (templates, cgmustache) => {
            const list = new dragAndDropList({
                setItems: function setItems(items) {
                    console.log('items: ', items);
                    element.setTableTotals(items);
                },
                allItems: allTableTotals,
                items: tableTotals,
                id: 'table-totals-dnd',
                renderTextInput: true,
                listClasses
            });
            const listHtml = await list.generateList();

            const collapsible = cgmustache.renderTemplate(templates, {
                'display': true,
                'title': 'Table Totals',
                'id': 'table-collapsible'
            }, "collapsible", {'content': listHtml});

            const tableTotalsInspector = document.getElementById(TableTotals.TABLE_TOTALS_INSPECTOR_ID);
            const template = cgmustache.renderTemplate(collapsible, {}, 'tableTotals');
            tableTotalsInspector.append(document.createRange().createContextualFragment(template));

            list.initList();
        });
    };

    return new TableTotals();
});