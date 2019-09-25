define([
    'InvoiceDesigner/Template/InspectorAbstract',
    'InvoiceDesigner/Template/Inspector/DomListener/TableCells',
    'InvoiceDesigner/Template/Inspector/Font',
    'InvoiceDesigner/dragAndDropList',
    'InvoiceDesigner/Template/Storage/Table',
    'InvoiceDesigner/Template/Element/Helpers/OrderTable',
    'InvoiceDesigner/Template/Inspector/Heading',
    'cg-mustache'
], function(
    InspectorAbstract,
    tableCellsDomListener,
    Font,
    dragAndDropList,
    TableStorage,
    orderTableHelper,
    Heading,
    CGMustache
) {
    const TEXT_FORMATTING_CLASS = 'inspector-text-format';

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
        this.COLUMN_WIDTH_ID = `${idPrefix}-column-width`;
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

        const cellNode = event.target;
        element.setActiveCellNodeId(cellNode.id);

        const tableCells = element.getTableCells();
        this.cellDataIndex = orderTableHelper.getCellDataIndexFromDomId(cellNode.id, tableCells);
        const cellData = tableCells[this.cellDataIndex];

        if (!cellData) {
            return;
        }

        const templateUrlMap = {
            select: '/channelgrabber/zf2-v4-ui/templates/elements/custom-select.mustache',
            colourPicker: '/channelgrabber/zf2-v4-ui/templates/elements/colour-picker.mustache',
            align: '/channelgrabber/zf2-v4-ui/templates/elements/align.mustache',
            collapsible: '/channelgrabber/zf2-v4-ui/templates/elements/collapsible.mustache',
            font: '/channelgrabber/settings/template/InvoiceDesigner/Template/Inspector/font.mustache',
            heading: '/channelgrabber/settings/template/InvoiceDesigner/Template/Inspector/heading.mustache'
        };

        CGMustache.get().fetchTemplates(templateUrlMap, (templates, cgmustache) => {
            this.cgmustache = cgmustache;

            this.applyHeader(templates);

            const textFormattingHTML = this.getTextFormattingHtml(element);
            const alignHTML = this.getAlignHTML(templates, cellData);
            const fontFamilyHTML = this.getFontFamilyHTML(templates, cellData);
            const fontSizeHTML = this.getFontSizeHTML(templates, cellData);
            const fontColorPickerHTML = this.getFontColorPickerHTML(templates, cellData);

            const backgroundColorPickerHTML = this.getBackgroundColorHTML(templates, cellData);
            const measurementUnitSelectHTML = this.getMeasurementUnitHTML(templates, cellData);

            const html = `<div class="inspector-holder"> 
                            <div class="u-defloat u-margin-top-med">
                                <div class="u-flex-left u-margin-bottom-xsmall">
                                    ${textFormattingHTML}
                                </div>
                                <div class="u-flex-left u-margin-bottom-xsmall">
                                    ${alignHTML}
                                </div>
                                <div class="u-margin-bottom-xsmall u-float-left">
                                    ${fontFamilyHTML}
                                </div>
                                <span class="u-float-left u-margin-bottom-xsmall">
                                    ${fontSizeHTML}
                                </span>
                                <span class="u-float-left u-margin-bottom-xsmall">
                                    ${fontColorPickerHTML}
                                </span>                                                      
                             </div>
                             
                             <div class="u-defloat u-margin-top-med u-inline-block">
                                <h2>Background Colour</h2>
                                ${backgroundColorPickerHTML}
                             </div>
                             
                             <div class="u-defloat u-margin-top-med u-inline-block"> 
                                <h2>Column Width</h2>
                                <div>
                                    If left blank ChannelGrabber will automatically adjust the width of the column to best fit the table.
                                </div>    
                                <div class="u-flex-v-center">
                                    <span>
                                        <input id="${this.COLUMN_WIDTH_ID}" class="inputbox u-width-80px" type="number" title="Column Width" />
                                    </span>
                                    <span>
                                        ${measurementUnitSelectHTML}
                                    </span>
                                </div>
                             </div>
                          </div>`;

            const tableCellsInspector = document.getElementById('tableCells-inspector');
            const tableCellsHTML = cgmustache.renderTemplate(html, {}, 'tableCells');
            tableCellsInspector.append(document.createRange().createContextualFragment(tableCellsHTML));

            tableCellsDomListener.init(this, element);
        });
    };

    TableCells.prototype.applyHeader = function(templates) {
        const headingHTML = this.cgmustache.renderTemplate(templates, {'type': "Table Cells"}, "heading");
        const headingContainerNode = document.querySelector(Heading.getHeadingInspectorSelector());
        headingContainerNode.innerHTML = headingHTML;
    };

    TableCells.prototype.getMeasurementUnitHTML = function(templates, cellData) {
        const measurementUnitData = this.getMeasurementUnitData();
        measurementUnitData.sizeClass = 'small';
        const measurementUnitSelectHTML = this.cgmustache.renderTemplate(templates, measurementUnitData, "select");
        return measurementUnitSelectHTML;
    };

    TableCells.prototype.getBackgroundColorHTML = function(templates, cellData) {
        const backgroundColorData = Font.getFontColourViewData(null, this.BACKGROUND_COLOR_ID);
        backgroundColorData.initialColour = cellData.backgroundColour;
        const backgroundColorPickerHTML = this.cgmustache.renderTemplate(templates, backgroundColorData, "colourPicker");
        return backgroundColorPickerHTML;
    };

    TableCells.prototype.getFontColorPickerHTML = function(templates, cellData) {
        const fontColorData = Font.getFontColourViewData(null, this.FONT_COLOR_ID);
        fontColorData.initialColour = cellData.fontColour;
        const fontColorHTML = this.cgmustache.renderTemplate(templates, fontColorData, "colourPicker");
        return fontColorHTML;
    };

    TableCells.prototype.getFontSizeHTML = function(templates, cellData) {
        const fontSizeData = Font.getFontSizeViewData(null, this.FONT_SIZE_ID);
        fontSizeData.sizeClass = 'u-width-100px';
        if (cellData.fontSize) {
            applyInitialSelection(fontSizeData, cellData, 'fontSize');
        }
        const fontSizeHTML = this.cgmustache.renderTemplate(templates, fontSizeData, "select");
        return fontSizeHTML;
    };

    TableCells.prototype.getAlignHTML = function(templates, cellData) {
        const fontAlignData = Font.getFontAlignViewData(null, this.FONT_ALIGN_ID);
        fontAlignData.containerClass = 'u-flex-left u-width-100pc';
        fontAlignData.showJustify = true;
        if (cellData.align) {
            fontAlignData[cellData.align] = true;
        }
        const alignHTML = this.cgmustache.renderTemplate(templates, fontAlignData, "align");
        return alignHTML;
    };

    TableCells.prototype.getFontFamilyHTML = function(templates, cellData) {
        const fontFamilyData = Font.getFontFamilyViewData(null, this.FONT_FAMILY_ID);
        if (cellData.fontFamily) {
            applyInitialSelection(fontFamilyData, cellData, 'fontFamily');
        }
        const fontFamilyHTML = this.cgmustache.renderTemplate(templates, fontFamilyData, "select");
        return fontFamilyHTML;
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

    TableCells.prototype. getTextFormattingHtml = function(element) {
        const currentCell = this.getCurrentCell(element);

        const getActive = (value) => {
            if (!value) {
                return '';
            }
            return 'inspector-text-format-label-active'
        };

        const boldActiveClass = getActive(currentCell['bold']);
        const italicActiveClass = getActive(currentCell['italic']);
        const underlineActiveClass = getActive(currentCell['underline']);

        return `<input class="${TEXT_FORMATTING_CLASS}-input" type="checkbox" id="${this.FONT_BOLD_ID}" name="${this.FONT_BOLD_ID}">
                <label class="${TEXT_FORMATTING_CLASS}-label ${boldActiveClass} inspector-text-format-label-bold" for="${this.FONT_BOLD_ID}" title="Bold"></label>

                <input class="${TEXT_FORMATTING_CLASS}-input" type="checkbox" id="${this.FONT_ITALIC_ID}" name="${this.FONT_ITALIC_ID}">
                <label class="${TEXT_FORMATTING_CLASS}-label ${italicActiveClass} inspector-text-format-label-italic" for="${this.FONT_ITALIC_ID}" title="Italic"></label>

                <input class="${TEXT_FORMATTING_CLASS}-input" type="checkbox" id="${this.FONT_UNDERLINE_ID}" name="${this.FONT_UNDERLINE_ID}">
                <label class="${TEXT_FORMATTING_CLASS}-label ${underlineActiveClass}  inspector-text-format-label-underline" for="${this.FONT_UNDERLINE_ID}" title="Underline"></label>`;
    };

    TableCells.prototype.getTableCellProperty = function(element, property) {
        const currentCell = this.getCurrentCell(element);
        return currentCell[property];
    };

    TableCells.prototype.setTableCellProperty = function(element, property, value) {
        const tableCells = element.getTableCells();
        const cellToAffect = tableCells[this.cellDataIndex];
        cellToAffect[property] = value;
        element.setTableCells(tableCells);
    };

    TableCells.prototype.getCurrentCell = function(element) {
        const tableCells = element.getTableCells();
        const cellToAffect = tableCells[this.cellDataIndex];
        return cellToAffect;
    };

    TableCells.prototype.toggleProperty = function(element, property, input) {
        const currentValue = this.getTableCellProperty(element, property);
        const valueToSet = typeof currentValue === 'boolean' ? !currentValue : true;
        const activeClass = `${TEXT_FORMATTING_CLASS}-label-active`;

        this.setTableCellProperty(element, property, valueToSet)

        const inputLabel = input.labels[0];

        if (inputLabel.classList.contains(activeClass)) {
            inputLabel.classList.remove(activeClass);
            return;
        }
        inputLabel.classList.add(activeClass);
    };

    TableCells.prototype.setColumnWidth = function(element, value) {
        const currentCell = this.getCurrentCell(element);
        const tableColumns = element.getTableColumns().slice();

        const columnIndexForCurrentCell = getColumnIndexForCurrentCell(tableColumns, currentCell);

        tableColumns[columnIndexForCurrentCell].width = parseInt(value);
        element.setTableColumns(tableColumns);
    };

    TableCells.prototype.setWidthMeasurementUnit = function(element, value) {
        const currentCell = this.getCurrentCell(element);
        const tableColumns = element.getTableColumns().slice();
        const columnIndexForCurrentCell = getColumnIndexForCurrentCell(tableColumns, currentCell);

        tableColumns[columnIndexForCurrentCell].widthMeasurementUnit = value;
        element.setTableColumns(tableColumns);
    };

    TableCells.prototype.setFontFamily = function(element, fontFamily) {
        this.setTableCellProperty(element, 'fontFamily', fontFamily);
    };

    TableCells.prototype.setFontSize = function(element, fontSize) {
        this.setTableCellProperty(element, 'fontSize', fontSize);
    };

    TableCells.prototype.setAlign = function(element, align, input) {
        this.setTableCellProperty(element, 'align', align);
    };

    TableCells.prototype.setFontColour = function(element, colour) {
        this.setTableCellProperty(element, 'colour', colour);
    };

    TableCells.prototype.setBackgroundColour = function(element, colour) {
        this.setTableCellProperty(element, 'backgroundColour', colour);
    };

    return new TableCells();

    function isCellClick(event) {
        const clickedElement = event.target;
        const tag = clickedElement.tagName.toLowerCase();
        return tag === 'th' || tag === 'td';
    }

    function getColumnIndexForCurrentCell(tableColumns, currentCell) {
        return tableColumns.findIndex(column => {
            return column.id === currentCell.column
        });
    }
    
    function applyInitialSelection(data, cellData, property) {
        const initialOption = data.options.find(option => {
            return option.value == cellData[property];
        });
        if (!initialOption) {
            return;
        }
        data.initialTitle = initialOption.title;
        data.initialValue = initialOption.value;
    }
});