define([
    'InvoiceDesigner/Template/InspectorAbstract',
    'InvoiceDesigner/Template/Inspector/Font',
    'InvoiceDesigner/dragAndDropList',
    'InvoiceDesigner/Template/Storage/Table',
    'cg-mustache'
], function(
    InspectorAbstract,
    Font,
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

        //todo - get the relevant tableCells data...
        // here...

        let cellNode = event.target;

        element.setActiveCellNodeId(cellNode.id);

        const templateUrlMap = {
            select: '/channelgrabber/zf2-v4-ui/templates/elements/custom-select.mustache',
            colourPicker: '/channelgrabber/zf2-v4-ui/templates/elements/colour-picker.mustache',
            align: '/channelgrabber/zf2-v4-ui/templates/elements/align.mustache',
            collapsible: '/channelgrabber/zf2-v4-ui/templates/elements/collapsible.mustache',
            font: '/channelgrabber/settings/template/InvoiceDesigner/Template/Inspector/font.mustache',

        };

        CGMustache.get().fetchTemplates(templateUrlMap, async (templates, cgmustache) => {

//            var templateUrlMap = {
//                select: '/channelgrabber/zf2-v4-ui/templates/elements/custom-select.mustache',
//                colourPicker: '/channelgrabber/zf2-v4-ui/templates/elements/colour-picker.mustache',
//                align: '/channelgrabber/zf2-v4-ui/templates/elements/align.mustache',
//                font: '/channelgrabber/settings/template/InvoiceDesigner/Template/Inspector/font.mustache',
//                collapsible: '/channelgrabber/zf2-v4-ui/templates/elements/collapsible.mustache'
//            };
//            CGMustache.get().fetchTemplates(templateUrlMap, function(templates, cgmustache)
//            {
            //todo - pass params into these when we know the format for the saved data against cells
            let fontSizeData = Font.getFontSizeViewData();
            let fontFamilyData = Font.getFontFamilyViewData();
            let fontColorData = Font.getFontColourViewData();

            var fontSize = cgmustache.renderTemplate(templates, fontSizeData, "select");
            var fontFamily = cgmustache.renderTemplate(templates, fontFamilyData, "select");
            var fontColour = cgmustache.renderTemplate(templates, fontColorData, "colourPicker");
            var align = cgmustache.renderTemplate(templates, null, "align");
            var font = cgmustache.renderTemplate(templates, {}, "font", {
                'fontSize': fontSize,
                'fontFamily': fontFamily,
                'fontColour': fontColour,
                'align': align
            });
            console.log('font: ', font);
            
            const html = `<div class="inspector-holder"> 
                            in the cellInspector
                            <span class="heading-medium">Font</span>
                            ${font}
                          </div>`;


            const collapsible = cgmustache.renderTemplate(templates, {
                'display': true,
                'title': 'Table Cell',
                'id': 'table-cell-collapsible'
            }, "collapsible", {'content': html});

            const tableCellsInspector = document.getElementById('tableCells-inspector');
            const template = cgmustache.renderTemplate(collapsible, {}, 'tableCells');
            tableCellsInspector.append(document.createRange().createContextualFragment(template));
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