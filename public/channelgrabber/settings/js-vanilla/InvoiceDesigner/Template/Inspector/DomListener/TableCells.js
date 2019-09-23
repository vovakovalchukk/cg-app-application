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

//
//        $('#' + FONT_FAMILY_ID).off('change').on('change', (event, selectBox, id) => {
//            console.log('on change');
//            inspector.setFontFamily(element, id);
//        });
//
//        $('#' + FONT_SIZE_ID).off('change').on('change', (event, selectBox, id) => {
//            inspector.setFontSize(element, id);
//        });
//
//        $('#' + FONT_ALIGN_ID).off('change').on('change', (event, align) => {
//            inspector.setAlign(element, align);
//        });
//
//        $('#' + FONT_COLOR_ID).off('change keyup paste').on('change keyup paste', () => {
//            inspector.setFontColour(element, this.getDomManipulator().getValue(this));
//        });
    };
    
    TableCellsDomListener.prototype.initTextFormattingHandlers = function(inspector, element) {
        console.log('in initTextFormattingHandlers');
        this.boldInput.onclick = event => {
            console.log('in underlineInput', {event, inspector})
            inspector.toggleBold(element);
        };
        this.italicInput.onclick = event => {
            console.log('in italicInput',event);
        };
        this.underlineInput.onclick = event => {
            console.log('in underlineInput', {event, inspector})
//            inspector.setBold()
        };
        this.fontFamilyInput.onchange = (event, selectBox, id) => {
            console.log('font family change');
            inspector.setFontFamily(element, id);
        };
        this.fontSizeInput.onchange = (event, selectBox, id) => {
            console.log('fontsize onchange');
            inspector.setFontSize(element, id);
        };
        this.fontAlignInput.onchange = (event, align) => {
            console.log('fotn align onchange');
            inspector.setAlign(element, align);
        };

        const colorInputChange = event => {
            const value = event.target.value;
            inspector.setFontColour(element, value);
        };
        this.fontColorInput.onchange = this.fontColorInput.onkeyup = this.fontColorInput.onpaste = colorInputChange;
    };

    return new TableCellsDomListener();
});