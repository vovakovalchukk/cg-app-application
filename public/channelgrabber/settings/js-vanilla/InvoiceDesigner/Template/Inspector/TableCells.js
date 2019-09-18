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
    let TableCells = function() {
        InspectorAbstract.call(this);

        this.setId('tableCells');
        this.setInspectedAttributes(['tableCells']);
    };

    TableCells.TABLE_COLUMNS_INSPECTOR_SELECTOR = '#tableCells-inspector';
    TableCells.TABLE_COLUMNS_CELL_ACTIVE_CLASS = 'invoice-designer-table-cell-active';

    TableCells.prototype = Object.create(InspectorAbstract.prototype);

    TableCells.prototype.hide = function() {
        this.getDomManipulator().render(TableCells.TABLE_COLUMNS_INSPECTOR_SELECTOR, "");
    };

    TableCells.prototype.showForElement = function(element, event) {
        if (!isCellClick(event)) {
            return;
        }
        this.nodeToColumnMap = this.createNodeToColumnMap(element, event);
        const relevantColumnData = this.getRelevantColumnData(element, event);
        let cellNode = event.target;

        element.setActiveCellNodeId(cellNode.id);

        const templateUrlMap = {
            collapsible: '/channelgrabber/zf2-v4-ui/templates/elements/collapsible.mustache'
        };

        CGMustache.get().fetchTemplates(templateUrlMap, async (templates, cgmustache) => {
            const html = `<div> in the cellInspector</div>`;
            const collapsible = cgmustache.renderTemplate(templates, {
                'display': true,
                'title': 'Table Cell',
                'id': 'table-cell-collapsible'
            }, "collapsible", {'content': html});

            const tableColumnsInspector = document.getElementById('tableCells-inspector');
            tableColumnsInspector.append(document.createRange().createContextualFragment(collapsible));
        });
    };

    TableCells.prototype.createNodeToColumnMap = function(element, event) {
        const map = new Map;
        const node = event.currentTarget;
        const tableElement = node.querySelector('table');

        const [headingRow, dataRow] = tableElement.querySelectorAll('tr');
        const tableColumns = element.getTableColumns();

        for (let index = 0; index < headingRow.children.length; index++) {
            map.set(headingRow.children[index], tableColumns[index]);
            map.set(dataRow.children[index], tableColumns[index]);
        }

        return map;
    };

    TableCells.prototype.getRelevantColumnData = function(element, event) {
        const clickedElement = event.target;
        return this.nodeToColumnMap.get(clickedElement);
    };

    return new TableCells();

    function isCellClick(event) {
        const clickedElement = event.target;
        const tag = clickedElement.tagName.toLowerCase();
        return tag === 'th' || tag === 'td';
    }
});