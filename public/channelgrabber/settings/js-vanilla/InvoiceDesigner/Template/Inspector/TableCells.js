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
        this.FONT_BOLD_ID = `${idPrefix}-font-bold`;
        this.FONT_ITALIC_ID = `${idPrefix}-font-italic`;
        this.FONT_UNDERLINE_ID = `${idPrefix}-font-underline`;
        this.BACKGROUND_COLOR_ID = `${idPrefix}-background-color`;
        this.MEASUREMENT_UNIT_ID = `${idPrefix}-measurement-unit`;
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
            const backgroundColorData = Font.getFontColourViewData(null, this.BACKGROUND_COLOR_ID);
            const fontAlignData = Font.getFontAlignViewData(null, this.FONT_ALIGN_ID);
            fontAlignData.containerClass = 'u-flex-left u-width-100pc';
            const measurementUnitData = this.getMeasurementUnitData();
            measurementUnitData.sizeClass = 'small';

            const fontSize = cgmustache.renderTemplate(templates, fontSizeData, "select");
            const fontFamily = cgmustache.renderTemplate(templates, fontFamilyData, "select");
            const fontColorPicker = cgmustache.renderTemplate(templates, fontColorData, "colourPicker");
//            const fontColorPicker = `<input type="color" id="${this.FONT_COLOR_ID}" name="font color">`;

            const align = cgmustache.renderTemplate(templates, fontAlignData, "align");
            const backgroundColorPicker = cgmustache.renderTemplate(templates, backgroundColorData, "colourPicker");
            const measurementUnitSelect = cgmustache.renderTemplate(templates, measurementUnitData, "select");

            const textFormatting = this.getTextFormattingHtml();

            const html = `<div class="inspector-holder"> 
                            <div class="u-defloat u-margin-top-med u-overflow-hidden">
                                <div>
                                    ${textFormatting}
                                </div>
                                
                                <div>${align}</div>
                                <div>${fontFamily}</div>
                                <span class="u-inline-block">${fontSize}</span>
                                <span class="u-inline-block">${fontColorPicker}</span>                                                      
                             </div>
                             
                             <div class="u-defloat u-margin-top-med u-overflow-hidden">
                                <h2>Background Colour</h2>
                                ${backgroundColorPicker}
                             </div>
                             
                             <div class="u-defloat u-margin-top-med u-overflow-hidden"> 
                                <h2>Column Width</h2>
                                <div>
                                    If left blank ChannelGrabber will automatically adjust the width of the column to best fit the table.
                                </div>    
                                <div class="u-flex-v-center">
                                    <span>
                                        <input class="inputbox u-width-80px" type="number" title="Column Width" />
                                    </span>
                                    <span>
                                        ${measurementUnitSelect}
                                    </span>
                                </div>
                             </div>
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

    TableCells.prototype.getMeasurementUnitData = function() {
        return {
            id: this.MEASUREMENT_UNIT_ID,
            name: 'table-cells-measurement-unit',
            options: [
                {
                    selected: true,
                    title: 'mm',
                    value: 'mm'
                },
                {
                    title: 'in',
                    value: 'in'
                }
            ],
            sizeClass: "u-width-80px"
        }
    };

    TableCells.prototype. getTextFormattingHtml = function() {
//        return `<div>
//                <input class="inspector-text-format-radio" type="checkbox" id="${this.FONT_BOLD_ID}" name="${this.FONT_BOLD_ID}">
//
//                <input class="inspector-text-format-radio" type="checkbox" id="${this.FONT_ITALIC_ID}" name="${this.FONT_ITALIC_ID}">
//
//                <input class="inspector-text-format-radio" type="checkbox" id="${this.FONT_UNDERLINE_ID}" name="${this.FONT_UNDERLINE_ID}">
//            </div>`;
        return `<div>
                <input class="inspector-text-format-radio" type="checkbox" id="${this.FONT_BOLD_ID}" name="${this.FONT_BOLD_ID}">
                <label class="inspector-text-format-label inspector-text-format-label-bold" for="${this.FONT_BOLD_ID}" title="Bold"></label>

                <input class="inspector-text-format-radio" type="checkbox" id="${this.FONT_ITALIC_ID}" name="${this.FONT_ITALIC_ID}">
                <label class="inspector-text-format-label inspector-text-format-label-italic" for="${this.FONT_ITALIC_ID}" title="Italic"></label>

                <input class="inspector-text-format-radio" type="checkbox" id="${this.FONT_UNDERLINE_ID}" name="${this.FONT_UNDERLINE_ID}">
                <label class="inspector-text-format-label inspector-text-format-label-underline" for="${this.FONT_UNDERLINE_ID}" title="Underline"></label>
            </div>`;
    }

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

    TableCells.prototype.getTableCellProperty = function(element, property) {
        const tableCells = element.getTableCells();
        const currentCell = tableCells[this.cellDataIndex];
        return currentCell[property];
    };

    TableCells.prototype.setTableCellProperty = function(element, property, value) {
        const tableCells = element.getTableCells();
        const cellToAffect = tableCells[this.cellDataIndex];
        cellToAffect[property] = value;
        element.setTableCells(tableCells);
    };

    TableCells.prototype.toggleBold = function(element) {
        console.log('in toggleBold');
        
        
        const currentBold = this.getTableCellProperty(element, 'bold');
        const boldToSet = typeof currentBold === 'boolean' ? !currentBold : true;
        // need to do an inverse
        this.setTableCellProperty(element, 'bold', boldToSet)
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