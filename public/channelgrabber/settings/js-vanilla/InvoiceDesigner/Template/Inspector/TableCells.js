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

    function getTextFormattingHtml() {

        return `<div>
                <input class="inspector-text-format-radio" type="checkbox" id="text-bold" name="text-bold">
                <label class="inspector-text-format-label inspector-text-format-label-bold" htmlFor="text-bold" title="Bold"></label>
                
                <input class="inspector-text-format-radio" type="checkbox" id="text-italic" name="text-italic">
                <label class="inspector-text-format-label inspector-text-format-label-italic" htmlFor="text-italic" title="Italic"></label>

                <input class="inspector-text-format-radio" type="checkbox" id="text-underline" name="text-underline">
                <label class="inspector-text-format-label inspector-text-format-label-underline" htmlFor="text-underline" title="Underline"></label>
            </div>`;
    }
    TableCells.prototype.showForElement = function(element, event) {
        if (!isCellClick(event)) {
            return;
        }

        let cellNode = event.target;
        element.setActiveCellNodeId(cellNode.id);

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
            const fontSizeData = Font.getFontSizeViewData(null, this.FONT_SIZE_ID);
            fontSizeData.sizeClass = 'u-width-100px';
            const fontFamilyData = Font.getFontFamilyViewData(null, this.FONT_FAMILY_ID);
            const fontColorData = Font.getFontColourViewData(null, this.FONT_COLOR_ID);
            const fontAlignData = Font.getFontAlignViewData(null, this.FONT_ALIGN_ID);
            fontAlignData.containerClass = 'u-flex-left u-width-100pc';

            const fontSize = cgmustache.renderTemplate(templates, fontSizeData, "select");
            const fontFamily = cgmustache.renderTemplate(templates, fontFamilyData, "select");
            const fontColour = cgmustache.renderTemplate(templates, fontColorData, "colourPicker");
            const align = cgmustache.renderTemplate(templates, fontAlignData, "align");

            let textFormatting = getTextFormattingHtml()

            const html = `<div class="inspector-holder"> 
                            <span class="heading-medium">Font</span>
                            <div>
                                ${textFormatting}
                            </div>
                            
                            <div>${align}</div>
                            <div>${fontFamily}</div>
                            <div>
                                <span class="u-inline-block">${fontSize}</span>
                                <span class="u-inline-block">${fontColour}</span>                                                       </div>
                          </div>`;

//            const html = `<div class="inspector-holder">
//                            in the cellInspector
//                            <span class="heading-medium">Font</span>
//                            ${font}
//                          </div>`;



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

    TableCells.prototype.getFontHTML = function(cgmustache, templates) {
        const fontSizeData = Font.getFontSizeViewData(null, this.FONT_SIZE_ID);
        const fontFamilyData = Font.getFontFamilyViewData(null, this.FONT_FAMILY_ID);
        const fontColorData = Font.getFontColourViewData(null, this.FONT_COLOR_ID);
        const fontAlignData = Font.getFontAlignViewData(null, this.FONT_ALIGN_ID);

        const fontSize = cgmustache.renderTemplate(templates, fontSizeData, "select");
        const fontFamily = cgmustache.renderTemplate(templates, fontFamilyData, "select");
        const fontColour = cgmustache.renderTemplate(templates, fontColorData, "colourPicker");
        const align = cgmustache.renderTemplate(templates, fontAlignData, "align");

        const font = cgmustache.renderTemplate(templates, {
                'ignoreHolder': true
            },
            "font", {
                'fontSize': fontSize,
                'fontFamily': fontFamily,
                'fontColour': fontColour,
                'align': align
            });

        return font;
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