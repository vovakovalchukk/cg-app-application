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

        const idPrefix = 'tableCells';

        this.FONT_FAMILY_ID = `${idPrefix}-font-family`;
        this.FONT_SIZE_ID = `${idPrefix}-font-size`;
        this.FONT_ALIGN_ID = `${idPrefix}-font-align`;
        this.FONT_COLOR_ID = `${idPrefix}-font-color`;
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

        let cellNode = event.target;
        element.setActiveCellNodeId(cellNode.id);

        let customEvent = new CustomEvent(
            "tableCellClick",
            {
                detail: {
                    cellNode,
                    element
                },
                bubbles: true,
                cancelable: true
            }
        );

        document.getElementById(cellNode.id).dispatchEvent(customEvent);

        const tableCells = element.getTableCells();
        this.cellDataIndex = orderTableHelper.getCellDataIndexFromDomId(cellNode.id, tableCells);

        const templateUrlMap = {
            select: '/channelgrabber/zf2-v4-ui/templates/elements/custom-select.mustache',
            colourPicker: '/channelgrabber/zf2-v4-ui/templates/elements/colour-picker.mustache',
            align: '/channelgrabber/zf2-v4-ui/templates/elements/align.mustache',
            collapsible: '/channelgrabber/zf2-v4-ui/templates/elements/collapsible.mustache',
            font: '/channelgrabber/settings/template/InvoiceDesigner/Template/Inspector/font.mustache'
        };

        CGMustache.get().fetchTemplates(templateUrlMap, (templates, cgmustache) => {
            const font = this.getFontHTML(cgmustache, templates);

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

    //todo - pass params into these when we know the format for the saved data against cells
    TableCells.prototype.getFontHTML = function(cgmustache, templates) {
        const fontSizeData = Font.getFontSizeViewData(null, this.FONT_SIZE_ID);
        const fontFamilyData = Font.getFontFamilyViewData(null, this.FONT_FAMILY_ID);
        const fontColorData = Font.getFontColourViewData(null, this.FONT_COLOR_ID);
        const fontAlignData = Font.getFontAlignViewData(null, this.FONT_ALIGN_ID);

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

    TableCells.prototype.setTableCellProperty = function(element, property, value) {
        const tableCells = element.getTableCells();
        const cellToAffect = tableCells[this.cellDataIndex];
        cellToAffect[property] = value;
        element.setTableCells(tableCells);
    };

    TableCells.prototype.setFontFamily = function(element, fontFamily) {
        this.setTableCellProperty(element, 'fontFamily', fontFamily);
    };

    TableCells.prototype.setFontSize = function(element, fontSize) {
        this.setTableCellProperty(element, 'fontSize', fontSize);
    };

    TableCells.prototype.setAlign = function(element, align) {
        this.setTableCellProperty(element, 'align', align);
    };

    TableCells.prototype.setFontColour = function(element, colour) {
        this.setTableCellProperty(element, 'colour', colour);
    };

    return new TableCells();

    function isCellClick(event) {
        const clickedElement = event.target;
        const tag = clickedElement.tagName.toLowerCase();
        return tag === 'th' || tag === 'td';
    }
});