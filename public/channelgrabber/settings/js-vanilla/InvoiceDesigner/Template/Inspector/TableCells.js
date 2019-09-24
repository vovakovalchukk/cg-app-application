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

        let cellNode = event.target;
        element.setActiveCellNodeId(cellNode.id);

        const tableCells = element.getTableCells();
        this.cellDataIndex = orderTableHelper.getCellDataIndexFromDomId(cellNode.id, tableCells);

        const templateUrlMap = {
            select: '/channelgrabber/zf2-v4-ui/templates/elements/custom-select.mustache',
            colourPicker: '/channelgrabber/zf2-v4-ui/templates/elements/colour-picker.mustache',
            align: '/channelgrabber/zf2-v4-ui/templates/elements/align.mustache',
            collapsible: '/channelgrabber/zf2-v4-ui/templates/elements/collapsible.mustache',
            font: '/channelgrabber/settings/template/InvoiceDesigner/Template/Inspector/font.mustache',
            heading: '/channelgrabber/settings/template/InvoiceDesigner/Template/Inspector/heading.mustache'
        };

        CGMustache.get().fetchTemplates(templateUrlMap, (templates, cgmustache) => {
            const fontSizeData = Font.getFontSizeViewData(null, this.FONT_SIZE_ID);
            fontSizeData.sizeClass = 'u-width-100px';
            const fontFamilyData = Font.getFontFamilyViewData(null, this.FONT_FAMILY_ID);
            const fontColorData = Font.getFontColourViewData(null, this.FONT_COLOR_ID);
            const backgroundColorData = Font.getFontColourViewData(null, this.BACKGROUND_COLOR_ID);

            const fontAlignData = Font.getFontAlignViewData(null, this.FONT_ALIGN_ID);
            fontAlignData.containerClass = 'u-flex-left u-width-100pc';
            fontAlignData.showJustify = true;

            const measurementUnitData = this.getMeasurementUnitData();
            measurementUnitData.sizeClass = 'small';

            const fontSize = cgmustache.renderTemplate(templates, fontSizeData, "select");
            const fontFamily = cgmustache.renderTemplate(templates, fontFamilyData, "select");
            const fontColorPicker = cgmustache.renderTemplate(templates, fontColorData, "colourPicker");

            const align = cgmustache.renderTemplate(templates, fontAlignData, "align");
            const backgroundColorPicker = cgmustache.renderTemplate(templates, backgroundColorData, "colourPicker");
            const measurementUnitSelect = cgmustache.renderTemplate(templates, measurementUnitData, "select");

            const textFormatting = this.getTextFormattingHtml(element);

            const heading = cgmustache.renderTemplate(templates, {'type' : "Table Cells"}, "heading");
            const headingNode = document.querySelector(Heading.getHeadingInspectorSelector());
            headingNode.innerHTML = heading;

            const html = `<div class="inspector-holder"> 
                            <div class="u-defloat u-margin-top-med u-overflow-hidden">
                                <div>
                                    ${textFormatting}
                                </div>
                                
                                <div class="u-flex-left">${align}</div>
                                <div>${fontFamily}</div>
                                <span class="u-inline-block">${fontSize}</span>
                                <span class="u-inline-block">${fontColorPicker}</span>                                                      
                             </div>
                             
                             <div class="u-defloat u-margin-top-med u-inline-block">
                                <h2>Background Colour</h2>
                                ${backgroundColorPicker}
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
                                        ${measurementUnitSelect}
                                    </span>
                                </div>
                             </div>
                          </div>`;

            const tableCellsInspector = document.getElementById('tableCells-inspector');
            const template = cgmustache.renderTemplate(html, {}, 'tableCells');
            tableCellsInspector.append(document.createRange().createContextualFragment(template));

            tableCellsDomListener.init(this, element);
        });
    };

    TableCells.prototype

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
        //todo - need to style based on active setting
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

        // todo - change classnames from radio button as it is no longer semantic
        return `<div>
                <input class="${TEXT_FORMATTING_CLASS}-input" type="checkbox" id="${this.FONT_BOLD_ID}" name="${this.FONT_BOLD_ID}">
                <label class="${TEXT_FORMATTING_CLASS}-label ${boldActiveClass} inspector-text-format-label-bold" for="${this.FONT_BOLD_ID}" title="Bold"></label>

                <input class="${TEXT_FORMATTING_CLASS}-input" type="checkbox" id="${this.FONT_ITALIC_ID}" name="${this.FONT_ITALIC_ID}">
                <label class="${TEXT_FORMATTING_CLASS}-label ${italicActiveClass} inspector-text-format-label-italic" for="${this.FONT_ITALIC_ID}" title="Italic"></label>

                <input class="${TEXT_FORMATTING_CLASS}-input" type="checkbox" id="${this.FONT_UNDERLINE_ID}" name="${this.FONT_UNDERLINE_ID}">
                <label class="${TEXT_FORMATTING_CLASS}-label ${underlineActiveClass}  inspector-text-format-label-underline" for="${this.FONT_UNDERLINE_ID}" title="Underline"></label>
            </div>`;
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
        const alignArray = ['left', 'center', 'right', 'justify'];
        const alignIndex = alignArray.indexOf(align);

        const alignIconClicked = input.children[alignIndex];
        this.setTableCellProperty(element, 'align', align);

        alignIconClicked.classList.add(`${this.FONT_ALIGN_ID}-active`);
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
});