define([
    'InvoiceDesigner/Template/InspectorAbstract',
    'InvoiceDesigner/Template/Inspector/DomListener/TableCells',
    'InvoiceDesigner/Template/Inspector/Font',
    'InvoiceDesigner/dragAndDropList',
    'InvoiceDesigner/Template/Storage/Table',
    'InvoiceDesigner/Template/Element/Helpers/OrderTable',
    'cg-mustache'
], function(
    InspectorAbstract,
    tableCellsDomListener,
    Font,
    dragAndDropList,
    TableStorage,
    orderTableHelper,
    CGMustache
) {
    let TableCells = function() {
        InspectorAbstract.call(this);

        this.setId('tableCells');
        this.setInspectedAttributes(['tableCells']);
    };

    TableCells.TABLE_COLUMNS_INSPECTOR_SELECTOR = '#tableCells-inspector';
    TableCells.TABLE_COLUMNS_CELL_ACTIVE_CLASS = 'invoice-designer-table-cell-active';

    //TODO - put ids in here
    TableCells.FONT_FAMILY_ID = '';
    TableCells.FONT_SIZE_ID = '';
    TableCells.FONT_ALIGN_ID ='';
    TableCells.FONT_COLOR_ID = '';

    TableCells.prototype = Object.create(InspectorAbstract.prototype);

    TableCells.prototype.hide = function() {
        this.getDomManipulator().render(TableCells.TABLE_COLUMNS_INSPECTOR_SELECTOR, "");
    };

    TableCells.prototype.showForElement = function(element, event) {
        if (!isCellClick(event)) {
            return;
        }
        this.nodeToColumnMap = this.createNodeToColumnMap(element, event);
        //todo - get the relevant tableCells data...
        // here...

        let cellNode = event.target;
        element.setActiveCellNodeId(cellNode.id);
        const tableCells = element.getTableCells();
        this.cellData = orderTableHelper.getCellDataFromDomId(cellNode.id, tableCells);


        const templateUrlMap = {
            select: '/channelgrabber/zf2-v4-ui/templates/elements/custom-select.mustache',
            colourPicker: '/channelgrabber/zf2-v4-ui/templates/elements/colour-picker.mustache',
            align: '/channelgrabber/zf2-v4-ui/templates/elements/align.mustache',
            collapsible: '/channelgrabber/zf2-v4-ui/templates/elements/collapsible.mustache',
            font: '/channelgrabber/settings/template/InvoiceDesigner/Template/Inspector/font.mustache'
        };

        CGMustache.get().fetchTemplates(templateUrlMap, (templates, cgmustache) => {
            const font = getFontHTML(cgmustache, templates);

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

            tableCellsDomListener.init(this, element);
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

    TableCells.prototype.setFontFamily = function(element, fontFamily) {
        //todo - these need to set the fontFamily on the tableCells.
//        element.setFontFamily(fontFamily);
    };

    TableCells.prototype.setFontSize = function(element, fontSize) {
//        element.setFontSize(fontSize);
    };

    TableCells.prototype.setAlign = function(element, align) {
//        element.setAlign(align);
    };

    TableCells.prototype.setFontColour = function(element, colour) {
//        element.setFontColour(colour);
    };

    return new TableCells();

    function isCellClick(event) {
        const clickedElement = event.target;
        const tag = clickedElement.tagName.toLowerCase();
        return tag === 'th' || tag === 'td';
    }

    //todo - pass params into these when we know the format for the saved data against cells
    function getFontHTML(cgmustache, templates) {
        const fontSizeData = Font.getFontSizeViewData();
        const fontFamilyData = Font.getFontFamilyViewData();
        const fontColorData = Font.getFontColourViewData();
        const fontAlignData = Font.getFontAlignViewData();

        const fontSize = cgmustache.renderTemplate(templates, fontSizeData, "select");
        const fontFamily = cgmustache.renderTemplate(templates, fontFamilyData, "select");
        const fontColour = cgmustache.renderTemplate(templates, fontColorData, "colourPicker");
        const align = cgmustache.renderTemplate(templates, fontAlignData, "align");
        const font = cgmustache.renderTemplate(templates, {}, "font", {
            'fontSize': fontSize,
            'fontFamily': fontFamily,
            'fontColour': fontColour,
            'align': align
        });
        return font;
    }
});