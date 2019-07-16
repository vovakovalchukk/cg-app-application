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

        const topMarginInput = document.getElementById(PrintPage.TOP_MARGIN_INPUT_SELECTOR)
        topMarginInput.addEventListener('change', event => {
            console.log('event top: ', event);
            const value = event.target.value;
            self.getModule().setPrintPageMargin('top', value);


        });


    };

    return PrintPage;
});