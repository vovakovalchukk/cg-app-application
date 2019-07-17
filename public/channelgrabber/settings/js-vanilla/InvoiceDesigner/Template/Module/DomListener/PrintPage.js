define([
    'module',
    'InvoiceDesigner/Module/DomListenerAbstract'
], function(
    requireModule,
    DomListenerAbstract
) {
    var PrintPage = function()
    {
        DomListenerAbstract.call(this);
    };

    PrintPage.prototype = Object.create(DomListenerAbstract.prototype);

    PrintPage.TOP_MARGIN_INPUT_SELECTOR = 'printPageTopMargin';
    PrintPage.BOTTOM_MARGIN_INPUT_SELECTOR = 'printPageBottomMargin';
    PrintPage.LEFT_MARGIN_INPUT_SELECTOR = 'printPageLeftMargin';
    PrintPage.RIGHT_MARGIN_INPUT_SELECTOR = 'printPageRightMargin';


    PrintPage.prototype.init = function(module)
    {
        var self = this;
        DomListenerAbstract.prototype.init.call(this, module);

        this.topMarginInput = document.getElementById(PrintPage.TOP_MARGIN_INPUT_SELECTOR);
        this.bottomMarginInput = document.getElementById(PrintPage.BOTTOM_MARGIN_INPUT_SELECTOR);
        this.leftMarginInput = document.getElementById(PrintPage.LEFT_MARGIN_INPUT_SELECTOR);
        this.rightMarginInput = document.getElementById(PrintPage.RIGHT_MARGIN_INPUT_SELECTOR);


        this.topMarginInput.addEventListener('change', event => {
            const value = event.target.value;
            self.getModule().setPrintPageMargin('top', value);
        });

        this.bottomMarginInput.addEventListener('change', event => {
            const value = event.target.value;
            self.getModule().setPrintPageMargin('bottom', value);
        });

        this.leftMarginInput.addEventListener('change', event => {
            const value = event.target.value;
            self.getModule().setPrintPageMargin('left', value);
        });

        this.rightMarginInput.addEventListener('change', event => {
            const value = event.target.value;
            self.getModule().setPrintPageMargin('right', value);
        });
    };

    return PrintPage;
});