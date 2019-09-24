define([
    'jquery',
    'InvoiceDesigner/Template/DomManipulator'
], function(
    $,
    domManipulator
) {
    const TableCellsDomListener = function()
    {
        this.getDomManipulator = function()
        {
            return domManipulator;
        };
    };

    TableCellsDomListener.prototype.init = function(inspector, element)
    {
        let {
            FONT_FAMILY_ID,
            FONT_SIZE_ID,
            FONT_ALIGN_ID,
            FONT_COLOR_ID,
            FONT_BOLD_ID,
            FONT_UNDERLINE_ID,
            FONT_ITALIC_ID,
            BACKGROUND_COLOR_ID,
            COLUMN_WIDTH_ID,
            MEASUREMENT_UNIT_ID
        } = inspector;

        this.boldInput = document.getElementById(FONT_BOLD_ID);
        this.italicInput = document.getElementById(FONT_ITALIC_ID);
        this.underlineInput = document.getElementById(FONT_UNDERLINE_ID);
        this.fontFamilyInput = document.getElementById(FONT_FAMILY_ID);
        this.fontSizeInput = document.getElementById(FONT_SIZE_ID);
        this.fontAlignInput = document.getElementById(FONT_ALIGN_ID);
        this.fontColorInput = document.getElementById(FONT_COLOR_ID);

        this.initTextFormattingHandlers(inspector, element);

        this.backgroundColorInput = document.getElementById(BACKGROUND_COLOR_ID);

        this.initCellFormattingHandlers(inspector, element);

        this.columnWidthInput = document.getElementById(COLUMN_WIDTH_ID);
        this.measurementUnitInput = document.getElementById(MEASUREMENT_UNIT_ID);

        this.initColumnHandlers(inspector, element)
    };

    TableCellsDomListener.prototype.initTextFormattingHandlers = function(inspector, element) {
        this.boldInput.onclick = event => {
            inspector.toggleProperty(element, 'bold', this.boldInput);
        };
        this.italicInput.onclick = event => {
            inspector.toggleProperty(element, 'italic', this.italicInput);
        };
        this.underlineInput.onclick = event => {
            inspector.toggleProperty(element, 'underline', this.underlineInput);
        };
        this.fontFamilyInput.onchange = (event, selectBox, id) => {
            inspector.setFontFamily(element, id);
        };
        this.fontSizeInput.onchange = (event, selectBox, id) => {
            inspector.setFontSize(element, id);
        };
        this.fontAlignInput.onchange = (event, align) => {
            inspector.setAlign(element, align);
        };
        const colorInputChange = event => {
            const value = event.target.value;
            inspector.setFontColour(element, value);
        };
        this.fontColorInput.onchange = this.fontColorInput.onkeyup = this.fontColorInput.onpaste = colorInputChange;
    };

    TableCellsDomListener.prototype.initCellFormattingHandlers = function(inspector, element) {
        const backgroundColourChange = event => {
            const value = event.target.value;
            inspector.setBackgroundColour(element, value)
        };
        this.backgroundColorInput.onchange = this.backgroundColorInput.onkeyup = this.backgroundColorInput.onpaste = backgroundColourChange;
    };

    TableCellsDomListener.prototype.initColumnHandlers = function(inspector, element) {
        this.columnWidthInput.onchange = event => {
            const value = event.target.value;
            inspector.setColumnWidth(element, value);
        };
        this.measurementUnitInput.onchange = event => {
            console.log('in measurementunit input');
            const value = event.target.value;
            inspector.setWidthMeasurementUnit(element, value);
        };
    };

    return new TableCellsDomListener();
});